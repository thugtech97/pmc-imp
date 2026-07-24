<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;
use App\Notifications\SystemNotification;
use Illuminate\Support\Facades\Notification;

/**
 * Thin helper for firing in-app (bell) notifications from workflow controllers.
 *
 * Every method is a one-liner at the call site and stores a database
 * notification via App\Notifications\SystemNotification (no email is sent).
 * Failures are swallowed so a notification hiccup can never break a request's
 * status update.
 */
class Notifier
{
    /**
     * Notify a single user (typically the requestor of an MRS/IMF).
     *
     * @param  int|null  $userId
     * @param  array     $payload  see SystemNotification::toArray()
     */
    public static function toUser($userId, array $payload)
    {
        if (empty($userId)) {
            return;
        }

        $user = User::find($userId);

        if (!$user) {
            return;
        }

        self::dispatch(collect([$user]), $payload);
    }

    /**
     * Notify every active user holding a role, matched by role name
     * (e.g. "MCD Planner", "MCD Verifier", "MCD Approver").
     *
     * @param  string  $roleName
     * @param  array   $payload
     */
    public static function toRoleName($roleName, array $payload)
    {
        $role = Role::where('name', $roleName)->first();

        if (!$role) {
            return;
        }

        self::toRoleId($role->id, $payload);
    }

    /**
     * Notify every active user holding a role, matched by role id
     * (e.g. purchasers = role_id 9).
     *
     * @param  int    $roleId
     * @param  array  $payload
     */
    public static function toRoleId($roleId, array $payload)
    {
        if (empty($roleId)) {
            return;
        }

        $users = User::where('role_id', $roleId)
            ->where('is_active', 1)
            ->get();

        self::dispatch($users, $payload);
    }

    /**
     * Send the notification to a collection of users, guarding against empties.
     *
     * @param  \Illuminate\Support\Collection  $users
     * @param  array  $payload
     */
    protected static function dispatch($users, array $payload)
    {
        $users = $users->filter();

        if ($users->isEmpty()) {
            return;
        }

        try {
            Notification::send($users, new SystemNotification($payload));
        } catch (\Throwable $e) {
            // Never let a notification failure break the workflow action.
            logger()->error('Notifier failed: ' . $e->getMessage());
        }
    }
}
