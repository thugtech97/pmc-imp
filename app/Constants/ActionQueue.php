<?php

namespace App\Constants;

use Illuminate\Support\Facades\Auth;

/**
 * "What is sitting on my desk right now?" — answered once for the whole admin panel.
 *
 * Three things read from this one list, so they can never drift apart:
 *   - the red sidebar badge          (App\View\Composers\SidebarComposer)
 *   - the sort that floats those rows to the top of page 1 of a listing
 *   - the "NEEDS YOUR ACTION" flag drawn on the row itself
 *
 * So a badge reading 6 always means "6 flagged rows are waiting for you", and
 * those 6 are always the first rows you see.
 *
 * Statuses are listed most urgent first — that order IS the sort order.
 * A leading "*" makes an entry a "contains" match rather than an exact one
 * (a few MRS statuses carry trailing detail, e.g. "FULLY APPROVED - ...").
 * Matching is case-insensitive on both sides: SQL Server's default collation
 * already ignores case, and the PHP side does the same on purpose.
 */
class ActionQueue
{
    /** Module keys — one per listing family / backing table. */
    const IMF = 'IMF';   // inventory_requests
    const MRS = 'MRS';   // ecommerce_sales_headers (incl. the PA-DP queues)
    const PA  = 'PA';    // purchase_advice

    /** Rank handed to rows outside the role's queue, so they always sort last. */
    const NOT_QUEUED = 99;

    /**
     * module => role name => ordered statuses that role is expected to act on.
     *
     * A role missing from a module simply has no queue there: no badge, no
     * highlight, no re-ordering. That is deliberate for view-only desks
     * (the MCD Approver on IMF) and for Admin, who is a bystander everywhere.
     *
     * @return array
     */
    private static function map()
    {
        return [
            self::IMF => [
                // The Planner acts twice: the first review, then the stock code
                // once the MCD Verifier hands it back.
                'MCD Planner'         => Status::imfPlannerStages(),
                'MCD Verifier'        => [Status::FOR_VERIFICATION],
                'Planning Supervisor' => [Status::APPROVED_MCD],
                // MCD Approver is view-only on IMF now — no queue.
            ],

            self::MRS => [
                'MCD Planner' => [
                    '*FULLY APPROVED',                // signed off — still needs a PA
                    'HOLD (For MCD Planner re-edit)', // sent back for re-edit
                    '*REVISED MRS',                   // requestor resubmitted
                ],
                'MCD Verifier'       => ['APPROVED (MCD Planner) - MRS For Verification'],
                'MCD Approver'       => ['Verified (MCD Verifier) - PA For MCD Manager Approval'],
                // PA-DP: the officer delegates, then the canvasser receives.
                'Purchasing Officer' => ['APPROVED (MCD Approver) - PA for Delegation'],
                'Purchaser'          => ['(For Purchasing Receival)'],
            ],

            self::PA => [
                'MCD Planner'        => ['HOLD (For MCD Planner re-edit)'],
                'MCD Verifier'       => ['APPROVED (MCD PLANNER) - FOR VERIFICATION'],
                'MCD Approver'       => ['VERIFIED (MCD Verifier) - PA For MCD Manager APPROVAL'],
                'Purchasing Officer' => ['APPROVED (MCD Approver) - PA for Delegation'],
                'Purchaser'          => ['(For Purchasing Receival)'],
            ],
        ];
    }

    /**
     * The role's queue for a module, most urgent first. Empty when it has none.
     *
     * @param  string  $module
     * @param  string  $roleName
     * @return array
     */
    public static function statuses($module, $roleName)
    {
        $map = self::map();

        return isset($map[$module][$roleName]) ? $map[$module][$roleName] : [];
    }

    /**
     * Does this role have anything to act on in this module at all?
     *
     * @param  string  $module
     * @param  string  $roleName
     * @return bool
     */
    public static function has($module, $roleName)
    {
        return count(self::statuses($module, $roleName)) > 0;
    }

    /**
     * Position of a status inside the role's queue, or NOT_QUEUED when it is
     * not the role's turn. Mirrors orderCase() exactly.
     *
     * @param  string  $module
     * @param  string  $roleName
     * @param  string  $status
     * @return int
     */
    public static function rank($module, $roleName, $status)
    {
        $status = trim((string) $status);

        foreach (self::statuses($module, $roleName) as $i => $entry) {
            if (substr($entry, 0, 1) === '*') {
                if ($status !== '' && stripos($status, substr($entry, 1)) !== false) {
                    return $i;
                }
            } elseif (strcasecmp($status, $entry) === 0) {
                return $i;
            }
        }

        return self::NOT_QUEUED;
    }

    /**
     * @param  string  $module
     * @param  string  $roleName
     * @param  string  $status
     * @return bool
     */
    public static function isActionable($module, $roleName, $status)
    {
        return self::rank($module, $roleName, $status) !== self::NOT_QUEUED;
    }

    /**
     * Blade shorthand — is this row waiting on the signed-in user?
     *
     * @param  string  $module
     * @param  string  $status
     * @return bool
     */
    public static function isMine($module, $status)
    {
        return self::isActionable($module, self::currentRoleName(), $status);
    }

    /**
     * Role name of the signed-in user, '' when not signed in.
     *
     * @return string
     */
    public static function currentRoleName()
    {
        $user = Auth::user();

        return $user ? (string) optional($user->assign_role)->name : '';
    }

    /**
     * A "CASE WHEN ... THEN 0 ... ELSE 99 END" for orderByRaw(), putting the
     * role's queue on top. Null when the role has no queue here, so callers
     * can leave the listing's own default sort alone.
     *
     * $statusExpr is the column — or any SQL expression — holding the status.
     *
     * @param  string  $module
     * @param  string  $roleName
     * @param  string  $statusExpr
     * @return string|null
     */
    public static function orderCase($module, $roleName, $statusExpr = 'status')
    {
        $entries = self::statuses($module, $roleName);

        if (empty($entries)) {
            return null;
        }

        $sql = 'CASE';

        foreach ($entries as $i => $entry) {
            if (substr($entry, 0, 1) === '*') {
                $sql .= " WHEN {$statusExpr} LIKE '%" . self::escape(substr($entry, 1)) . "%' THEN {$i}";
            } else {
                $sql .= " WHEN {$statusExpr} = '" . self::escape($entry) . "' THEN {$i}";
            }
        }

        return $sql . ' ELSE ' . self::NOT_QUEUED . ' END';
    }

    /**
     * Narrow a query down to the role's queue — used for the sidebar badge and
     * the per-tab counters, so a count and the flagged rows are the same set.
     * A role with no queue here gets nothing rather than everything.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $module
     * @param  string  $roleName
     * @param  string  $column
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function scope($query, $module, $roleName, $column = 'status')
    {
        $entries = self::statuses($module, $roleName);

        if (empty($entries)) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function ($q) use ($entries, $column) {
            foreach ($entries as $entry) {
                if (substr($entry, 0, 1) === '*') {
                    $q->orWhere($column, 'like', '%' . substr($entry, 1) . '%');
                } else {
                    $q->orWhere($column, $entry);
                }
            }
        });
    }

    /**
     * The statuses are our own constants, never user input, but an apostrophe
     * slipping into one later should not become an injection.
     *
     * @param  string  $value
     * @return string
     */
    private static function escape($value)
    {
        return str_replace("'", "''", $value);
    }
}
