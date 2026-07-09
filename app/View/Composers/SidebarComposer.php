<?php

namespace App\View\Composers;

use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use App\Models\Ecommerce\PurchaseAdvice;
use App\Models\Ecommerce\SalesHeader;

class SidebarComposer
{
    public function compose(View $view): void
    {
        if (!Auth::check()) {
            $view->with('sidebarCounts', []);
            return;
        }

        $user     = Auth::user();
        $roleName = optional($user->assign_role)->name ?? '';
        $counts   = [];

        // -------------------------------------------------------
        // MCD Planner
        // -------------------------------------------------------
        if ($roleName === 'MCD Planner') {
            // MRS fully approved → planner needs to create a PA for them
            $counts['mrs_fully_approved'] = SalesHeader::where('status', 'like', '%FULLY APPROVED%')
                ->count();

            // PA records on HOLD → needs re-edit by planner
            $counts['pa_hold'] = PurchaseAdvice::where('status', 'HOLD (For MCD Planner re-edit)')
                ->count();
        }

        // -------------------------------------------------------
        // MCD Verifier
        // -------------------------------------------------------
        if ($roleName === 'MCD Verifier') {
            // MRS awaiting verifier action
            $counts['mrs_to_verify'] = SalesHeader::where('status', 'APPROVED (MCD Planner) - MRS For Verification')
                ->count();

            // PA awaiting verifier action
            $counts['pa_to_verify'] = PurchaseAdvice::where('status', 'APPROVED (MCD PLANNER) - FOR VERIFICATION')
                ->count();
        }

        // -------------------------------------------------------
        // MCD Approver
        // -------------------------------------------------------
        if ($roleName === 'MCD Approver') {
            // MRS awaiting approver action
            $counts['mrs_to_approve'] = SalesHeader::where('status', 'Verified (MCD Verifier) - PA For MCD Manager Approval')
                ->count();

            // PA awaiting approver action
            $counts['pa_to_approve'] = PurchaseAdvice::where('status', 'VERIFIED (MCD Verifier) - PA For MCD Manager APPROVAL')
                ->count();
        }

        // -------------------------------------------------------
        // Purchasing Officer
        // -------------------------------------------------------
        if ($roleName === 'Purchasing Officer') {
            // Old PA system (pa.index) — SalesHeader with for_pa=1
            $counts['pa_to_delegate'] = SalesHeader::where('for_pa', 1)
                ->whereNull('received_at')
                ->whereIn('status', [
                    'APPROVED (MCD Approver) - PA for Delegation',
                    '(For Purchasing Receival)',
                ])
                ->count();

            // SR PAs approved by MCD Approver with no canvasser assigned yet
            $counts['pa_no_canvasser'] = PurchaseAdvice::whereNull('mrs_id')
                ->whereNull('received_by')
                ->where('status', 'like', '%APPROVED (MCD APPROVER)%')
                ->count();
        }

        // -------------------------------------------------------
        // Purchaser / Canvasser
        // -------------------------------------------------------
        if ($roleName === 'Purchaser') {
            // PA DP - For Receival: uses SalesHeader (for_pa=1, is_pa=1), not PurchaseAdvice
            $counts['pa_to_receive'] = SalesHeader::where('received_by', $user->id)
                ->where('for_pa', 1)
                ->where('is_pa', 1)
                ->where('status', '(For Purchasing Receival)')
                ->count();

            // PA SR - For Receival: PurchaseAdvice not yet received
            $counts['mcd_pa_for_receival'] = PurchaseAdvice::where('received_by', $user->id)
                ->where('status', '(For Purchasing Receival)')
                ->whereNull('received_at')
                ->count();
        }

        $view->with('sidebarCounts', $counts);
    }
}
