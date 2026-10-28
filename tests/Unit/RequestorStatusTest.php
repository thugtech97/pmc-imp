<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Ecommerce\SalesDetail;
use App\Models\Ecommerce\SalesHeader;

/**
 * Pins the department-user (requestor) facing MRS status labels.
 *
 * The point of the mapping is that it reads the STORED STATUS only. verified_at /
 * approved_at / received_at are deliberately preserved across a hold so a re-issued PA
 * keeps its posted date, so anything keyed off them reports a held request as approved.
 */
class RequestorStatusTest extends TestCase
{
    private function label($status, array $attrs = [])
    {
        $mrs = new SalesHeader(array_merge(['status' => $status], $attrs));
        $mrs->status = $status;
        return $mrs->requestor_status;
    }

    private function group($status, array $attrs = [])
    {
        $mrs = new SalesHeader(array_merge(['status' => $status], $attrs));
        $mrs->status = $status;
        return $mrs->requestor_status_group;
    }

    public function test_wfs_stage_labels()
    {
        $this->assertSame('SAVED - NOT YET SUBMITTED', $this->label('SAVED'));
        $this->assertSame('draft', $this->group('SAVED'));

        $this->assertSame('SUBMITTED - FOR WFS APPROVAL', $this->label('POSTED'));

        $this->assertSame(
            'FOR WFS APPROVAL (LAST APPROVED BY EDGAR F. SAGARIO)',
            $this->label('IN-PROGRESS (Approved by Edgar F. Sagario) - WFS')
        );

        $this->assertSame(
            'ON HOLD IN WFS (HELD BY RGBELARDO)',
            $this->label('REQUEST ON-HOLD (Hold by rgbelardo) - WFS')
        );
        $this->assertSame('hold', $this->group('REQUEST ON-HOLD (Hold by rgbelardo) - WFS'));

        $this->assertSame(
            'APPROVED IN WFS - FOR MCD PLANNER',
            $this->label('FULLY APPROVED (Approved by jadetorres-dmngr) - WFS')
        );
    }

    public function test_mcd_stage_labels()
    {
        $this->assertSame(
            'FOR MCD VERIFICATION',
            $this->label('APPROVED (MCD Planner) - MRS For Verification')
        );

        $this->assertSame(
            'FOR MCD MANAGER APPROVAL',
            $this->label('Verified (MCD Verifier) - PA For MCD Manager Approval')
        );

        $this->assertSame(
            'APPROVED BY MCD MANAGER - FOR CANVASSER ASSIGNMENT',
            $this->label('APPROVED (MCD Approver) - PA for Delegation')
        );
        $this->assertSame('approved', $this->group('APPROVED (MCD Approver) - PA for Delegation'));
    }

    public function test_hold_states_are_distinguished()
    {
        // Back with the planner — nothing for the requestor to do.
        $this->assertSame(
            'ON HOLD - WITH MCD PLANNER FOR RE-EDIT',
            $this->label('HOLD (For MCD Planner re-edit)')
        );
        $this->assertSame('hold', $this->group('HOLD (For MCD Planner re-edit)'));

        // Back with the requestor — this one is actionable.
        $this->assertSame(
            'RETURNED TO YOU FOR REVISION - MCD PLANNER',
            $this->label('REQUEST ON HOLD (Hold by MCD Planner)')
        );
        $this->assertSame('action', $this->group('REQUEST ON HOLD (Hold by MCD Planner)'));

        $this->assertSame(
            'REVISED - FOR MCD PLANNER REVIEW',
            $this->label('REVISED MRS - 2026-08-01 09:12:33 AM')
        );
    }

    /** Preserved approval stamps must never override the current state. */
    public function test_stale_stamps_do_not_leak_into_the_label()
    {
        $stamps = [
            'verified_at' => '2026-07-01 09:00:00',
            'approved_at' => '2026-07-02 09:00:00',
        ];

        $this->assertSame(
            'ON HOLD - WITH MCD PLANNER FOR RE-EDIT',
            $this->label('HOLD (For MCD Planner re-edit)', $stamps)
        );
        $this->assertSame(
            'RETURNED TO YOU FOR REVISION - MCD PLANNER',
            $this->label('REQUEST ON HOLD (Hold by MCD Planner)', $stamps)
        );
        $this->assertSame(
            'REVISED - FOR MCD PLANNER REVIEW',
            $this->label('REVISED MRS - 2026-08-01 09:12:33 AM', $stamps)
        );
    }

    /** Cancelled is terminal and wins over every stamp the record still carries. */
    public function test_cancelled_is_terminal()
    {
        $this->assertSame(
            'CANCELLED (BY RUPERT JOSEPH D. TORTAL)',
            $this->label('REQUEST CANCELLED (Cancelled by Rupert Joseph D. Tortal) - WFS', [
                'received_at' => '2026-07-10 09:00:00',
                'approved_at' => '2026-07-02 09:00:00',
            ])
        );
        $this->assertSame('cancelled', $this->group('CANCELLED'));
        $this->assertSame('CANCELLED', $this->label('CANCELLED'));
    }

    /** Unmapped statuses fall through as stored rather than getting an invented label. */
    public function test_unknown_status_falls_through()
    {
        $this->assertSame('SOME NEW STATUS', $this->label('Some New Status'));
        $this->assertSame('process', $this->group('Some New Status'));
    }

    /** ordered/delivered pairs -> an MRS with those lines. A promo_id of 1 marks an on-hold line. */
    private function withLines($status, array $lines)
    {
        $mrs = new SalesHeader(['status' => $status]);
        $mrs->status = $status;
        $mrs->setRelation('items', collect(array_map(function ($line) {
            return new SalesDetail([
                'qty_ordered'   => $line[0],
                'qty_delivered' => $line[1],
                'promo_id'      => isset($line[2]) ? $line[2] : 0,
            ]);
        }, $lines)));
        return $mrs;
    }

    /**
     * The warehouse never rewrites the stored status when it hands goods over, so the
     * delivered quantities have to be overlaid or the requestor sits at "RECEIVED FOR
     * CANVASS" forever.
     */
    public function test_delivery_progress_overlays_the_canvass_stage()
    {
        $canvass = 'RECEIVED FOR CANVASS - 2026-09-01 10:00:00 AM';

        $none = $this->withLines($canvass, [[10, 0], [5, 0]]);
        $this->assertSame('RECEIVED FOR CANVASS', $none->requestor_status);
        $this->assertSame('approved', $none->requestor_status_group);

        $partial = $this->withLines($canvass, [[10, 7], [5, 0]]);
        $this->assertSame('PARTIALLY DELIVERED (7 OF 15)', $partial->requestor_status);
        $this->assertSame('partial', $partial->requestor_status_group);

        $full = $this->withLines($canvass, [[10, 10], [5, 5]]);
        $this->assertSame('FULLY DELIVERED', $full->requestor_status);
        $this->assertSame('delivered', $full->requestor_status_group);

        // Fractional quantities print without trailing zeros.
        $fraction = $this->withLines($canvass, [[2.5, 1.25]]);
        $this->assertSame('PARTIALLY DELIVERED (1.25 OF 2.5)', $fraction->requestor_status);
    }

    /** On-hold (promo) lines are outside the delivery count, as on the warehouse screen. */
    public function test_delivery_progress_ignores_on_hold_lines()
    {
        $mrs = $this->withLines('(For Purchasing Receival)', [[10, 10], [99, 0, 1]]);
        $this->assertSame('FULLY DELIVERED', $mrs->requestor_status);
    }

    /** Delivered qty only speaks once the request is out with purchasing; a hold or cancel still wins. */
    public function test_delivery_progress_never_overrides_an_upstream_state()
    {
        $held = $this->withLines('HOLD (For MCD Planner re-edit)', [[10, 10]]);
        $this->assertSame('ON HOLD - WITH MCD PLANNER FOR RE-EDIT', $held->requestor_status);

        $cancelled = $this->withLines('CANCELLED', [[10, 10]]);
        $this->assertSame('CANCELLED', $cancelled->requestor_status);
    }
}
