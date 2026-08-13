<?php

namespace App\View\Composers;

use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use App\Models\Ecommerce\PurchaseAdvice;
use App\Models\Ecommerce\SalesHeader;
use App\Models\Ecommerce\InventoryRequest;
use App\Constants\ActionQueue;

class SidebarComposer
{
    public function compose(View $view): void
    {
        if (!Auth::check()) {
            $view->with('sidebarCounts', []);
            return;
        }

        $user     = Auth::user();
        $roleName = ActionQueue::currentRoleName();

        // One count per module, all three straight from App\Constants\ActionQueue,
        // so a badge is always "this many flagged rows are waiting for you" — the
        // same rows the listing floats to the top and marks NEEDS YOUR ACTION.
        $counts = [
            ActionQueue::IMF => 0,
            ActionQueue::MRS => 0,
            ActionQueue::PA  => 0,
        ];

        if (ActionQueue::has(ActionQueue::IMF, $roleName)) {
            $counts[ActionQueue::IMF] = ActionQueue::scope(
                InventoryRequest::query(),
                ActionQueue::IMF,
                $roleName
            )->count();
        }

        if (ActionQueue::has(ActionQueue::MRS, $roleName)) {
            $mrs = ActionQueue::scope(SalesHeader::query(), ActionQueue::MRS, $roleName);

            if ($roleName === 'Purchasing Officer') {
                // PA-DP delegation queue: still unreceived and actually flagged for PA.
                $mrs->whereNull('received_at')->where('for_pa', 1);
            } elseif ($roleName === 'Purchaser') {
                // A canvasser only ever owns what was assigned to them.
                $mrs->where('received_by', $user->id)->where('for_pa', 1)->where('is_pa', 1);
            }

            $counts[ActionQueue::MRS] = $mrs->count();
        }

        if (ActionQueue::has(ActionQueue::PA, $roleName)) {
            $pa = ActionQueue::scope(PurchaseAdvice::query(), ActionQueue::PA, $roleName);

            if ($roleName === 'Purchasing Officer') {
                // SR PAs approved by the MCD Approver with no canvasser on them yet.
                $pa->whereNull('mrs_id')->whereNull('received_by');
            } elseif ($roleName === 'Purchaser') {
                $pa->where('received_by', $user->id)->whereNull('received_at');
            }

            $counts[ActionQueue::PA] = $pa->count();
        }

        $view->with('sidebarCounts', $counts);
    }
}
