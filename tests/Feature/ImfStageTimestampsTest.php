<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Constants\Status;
use App\Http\Middleware\SecureHeaders;
use App\Models\Ecommerce\InventoryRequest;
use App\Models\Ecommerce\InventoryRequestItems;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Every desk an IMF passes through must leave a dated signature, and the
 * printed form must show it.
 *
 * The signatory block used to name who acted without ever saying when, and the
 * two MCD Planner passes had no stamp at all. The department head's WFS date
 * was also lost: it shared approved_at with the Planning Supervisor, whose
 * approval overwrote it.
 *
 * Runs against the PMC-ECOM-TEST duplicate DB inside a rolled-back transaction.
 */
class ImfStageTimestampsTest extends TestCase
{
    private const PLANNER_ID = 5;   // MCD Planner

    /** @var int */
    private $errorReporting;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(SecureHeaders::class);

        $this->errorReporting = error_reporting();
        error_reporting($this->errorReporting & ~E_WARNING);

        config(['database.connections.sqlsrv.database' => 'PMC-ECOM-TEST']);
        DB::purge('sqlsrv');
        DB::reconnect('sqlsrv');
        DB::beginTransaction();
    }

    protected function tearDown(): void
    {
        DB::rollBack();
        error_reporting($this->errorReporting);
        parent::tearDown();
    }

    private function makeImf(array $overrides = [])
    {
        return InventoryRequest::create(array_merge([
            'user_id'    => self::PLANNER_ID,
            'status'     => Status::APPROVED_WFS,
            'department' => 'MAINTENANCE',
            'section'    => 'ELECTRICAL',
            'division'   => 'MILL',
            'priority'   => 'HIGH',
            'type'       => 'new',
        ], $overrides));
    }

    private function makeItem($imf, array $overrides = [])
    {
        return InventoryRequestItems::create(array_merge([
            'imf_no'           => $imf->id,
            'stock_code'       => 'TEST-CODE',
            'item_description' => 'Test IMF item',
            'brand'            => 'ACME',
            'OEM_ID'           => 'OEM-123',
            'UoM'              => 'PC',
            'usage_rate_qty'   => 4,
            'usage_frequency'  => 'MONTHLY',
            'min_qty'          => 1,
            'max_qty'          => 10,
            'purpose'          => 'Testing',
        ], $overrides));
    }

    private function userWithRole($roleName, $email)
    {
        $role = Role::where('name', $roleName)->first();
        $this->assertNotNull($role, $roleName . ' role missing - run the role migration.');

        return User::create([
            'name'      => 'Test ' . $roleName,
            'email'     => $email,
            'password'  => bcrypt('secret'),
            'role_id'   => $role->id,
            'is_active' => 1,
        ]);
    }

    /** The printed HTML, rendered exactly as the PDF endpoint builds it. */
    private function printedImf($imf)
    {
        $imf = $imf->fresh();

        return view('admin.ecommerce.inventory.generate-report', [
            'InventoryRequestData' => $imf,
            'items'                => $imf->items,
            'oldItems'             => collect(),
            'histories'            => $imf->histories()
                ->reorder('created_at', 'asc')
                ->orderBy('id', 'asc')
                ->get(),
        ])->render();
    }

    /** MCD Planner, first pass - the review is dated. */
    public function test_planner_review_is_stamped()
    {
        $imf  = $this->makeImf();
        $item = $this->makeItem($imf);

        $this->actingAs(User::find(self::PLANNER_ID))
            ->post(route('imf.action', $imf->id), [
                'action' => 'approve',
                'type'   => 'new',
                'lines'  => [$item->id => ['planner_remarks' => 'Looks good.']],
            ]);

        $imf = $imf->fresh();
        $this->assertEquals(Status::FOR_VERIFICATION, $imf->status);
        $this->assertNotNull($imf->planner_reviewed_at, 'The MCD Planner review left no timestamp.');
        // The stock-code pass has not happened yet, so it stays open.
        $this->assertNull($imf->planner_stock_at);
    }

    /** MCD Verifier - verification is dated. */
    public function test_verification_is_stamped()
    {
        $imf  = $this->makeImf(['status' => Status::FOR_VERIFICATION]);
        $item = $this->makeItem($imf);

        $this->actingAs($this->userWithRole('MCD Verifier', 'stamp.verifier.test@example.com'))
            ->post(route('imf.action', $imf->id), [
                'action' => 'approve',
                'type'   => 'new',
                'lines'  => [$item->id => ['inventory_code' => 'INV-001', 'item_class' => 'A', 'dlt' => '30']],
            ]);

        $imf = $imf->fresh();
        $this->assertEquals(Status::VERIFIED_MCD, $imf->status);
        $this->assertNotNull($imf->verified_at, 'The MCD Verifier left no timestamp.');
    }

    /** MCD Planner, second pass - the stock-code entry is dated separately. */
    public function test_planner_stock_code_pass_is_stamped()
    {
        $imf  = $this->makeImf([
            'status'              => Status::VERIFIED_MCD,
            'planner_reviewed_at' => now()->subDay(),
        ]);
        $item = $this->makeItem($imf, ['stock_code' => null]);

        $this->actingAs(User::find(self::PLANNER_ID))
            ->post(route('imf.action', $imf->id), [
                'action' => 'approve',
                'type'   => 'new',
                'lines'  => [$item->id => ['stock_code' => 'SC-STAMP-' . uniqid()]],
            ]);

        $imf = $imf->fresh();
        $this->assertEquals(Status::APPROVED_MCD, $imf->status);
        $this->assertNotNull($imf->planner_stock_at, 'The stock-code pass left no timestamp.');
        // The first pass keeps its own date rather than being moved forward.
        $this->assertTrue($imf->planner_reviewed_at->lt($imf->planner_stock_at));
    }

    /**
     * The Planning Supervisor's approval no longer eats the department head's
     * WFS approval date.
     */
    public function test_supervisor_approval_keeps_the_department_head_date()
    {
        $wfsApprovedAt = now()->subDays(3)->startOfSecond();

        $imf = $this->makeImf([
            'status'          => Status::APPROVED_MCD,
            'type'            => 'update',
            'approved_by'     => 'DEPT HEAD',
            'approved_at'     => $wfsApprovedAt,
            'wfs_approved_at' => $wfsApprovedAt,
        ]);
        $this->makeItem($imf, ['stock_code' => 'NO-SUCH-PRODUCT-CODE']);

        $supervisor = $this->userWithRole('Planning Supervisor', 'stamp.supervisor.test@example.com');

        $this->actingAs($supervisor)
            ->post(route('imf.action', $imf->id), ['action' => 'approve', 'type' => 'update']);

        $imf = $imf->fresh();
        $this->assertEquals(Status::APPROVED_SUPERVISOR, $imf->status);
        $this->assertNotNull($imf->supervisor_approved_at, 'The Planning Supervisor left no timestamp.');
        $this->assertEquals(
            $wfsApprovedAt->format('Y-m-d H:i:s'),
            $imf->dept_head_signed_at->format('Y-m-d H:i:s'),
            'The department head date was overwritten by the final approval.'
        );
    }

    /** Records approved before the per-stage stamps still print a date. */
    public function test_legacy_records_fall_back_to_approved_at()
    {
        $approvedAt = now()->subMonths(6)->startOfSecond();

        $imf = $this->makeImf([
            'status'               => Status::APPROVED_SUPERVISOR,
            'approved_at'          => $approvedAt,
            'approver_approved_by' => 'OLD SUPERVISOR',
        ]);

        $imf = $imf->fresh();
        $this->assertEquals(
            $approvedAt->format('Y-m-d H:i:s'),
            $imf->supervisor_signed_at->format('Y-m-d H:i:s')
        );
        // approved_at belongs to the Supervisor here, so it is not claimed as
        // the department head's date as well.
        $this->assertNull($imf->dept_head_signed_at);
    }

    /** Each signature on the printed form carries the date that desk acted. */
    public function test_printed_imf_shows_a_date_for_every_signatory()
    {
        $imf = $this->makeImf([
            'status'                 => Status::APPROVED_SUPERVISOR,
            'submitted_at'           => now()->subDays(9),
            'approved_by'            => 'DEPT HEAD',
            'wfs_approved_at'        => now()->subDays(8),
            'planner_approved_by'    => 'PLANNER',
            'planner_reviewed_at'    => now()->subDays(7),
            'planner_stock_at'       => now()->subDays(5),
            'verifier_approved_by'   => 'VERIFIER',
            'verified_at'            => now()->subDays(6),
            'approver_approved_by'   => 'SUPERVISOR',
            'supervisor_approved_at' => now()->subDays(4),
        ]);
        $this->makeItem($imf);

        $html = $this->printedImf($imf);
        $imf  = $imf->fresh();

        $this->assertStringContainsString('Prepared: ', $html);
        $this->assertStringContainsString(
            \Carbon\Carbon::parse($imf->submitted_at)->format('F d, Y h:i A'),
            $html
        );
        $this->assertStringContainsString($imf->wfs_approved_at->format('F d, Y h:i A'), $html);
        $this->assertStringContainsString($imf->planner_reviewed_at->format('F d, Y h:i A'), $html);
        $this->assertStringContainsString($imf->planner_stock_at->format('F d, Y h:i A'), $html);
        $this->assertStringContainsString(
            \Carbon\Carbon::parse($imf->verified_at)->format('F d, Y h:i A'),
            $html
        );
        $this->assertStringContainsString($imf->supervisor_approved_at->format('F d, Y h:i A'), $html);
    }

    /** A stage nobody has reached yet reads "Pending" rather than a blank line. */
    public function test_printed_imf_marks_unreached_stages_as_pending()
    {
        $imf = $this->makeImf();
        $this->makeItem($imf);

        $html = $this->printedImf($imf);

        $this->assertStringContainsString('Pending', $html);
    }

    /**
     * An IMF signed before the per-stage stamps existed says the date is not
     * recorded, rather than reading as though nobody has acted yet.
     */
    public function test_printed_imf_separates_an_unrecorded_date_from_a_pending_one()
    {
        $imf = $this->makeImf([
            'status'              => Status::FOR_VERIFICATION,
            'planner_approved_by' => 'LEGACY PLANNER',
        ]);
        $this->makeItem($imf);

        $html = $this->printedImf($imf);

        // The Planner signed but left no stamp...
        $this->assertStringContainsString('Not recorded', $html);
        // ...while the Verifier, who has not acted at all, is still pending.
        $this->assertStringContainsString('Pending', $html);
        // The stock-code pass never happened, so it is not offered as a line.
        $this->assertStringNotContainsString('Stock code: ', $html);
    }

    /**
     * submitted_at and approved_at were DATE columns, so an old record's exact
     * midnight is an absent time and must not print as 12:00 AM.
     */
    public function test_printed_imf_omits_a_time_that_was_never_recorded()
    {
        $imf = $this->makeImf([
            'status'               => Status::APPROVED_SUPERVISOR,
            'approved_at'          => now()->subMonths(4)->startOfDay(),
            'approver_approved_by' => 'OLD SUPERVISOR',
        ]);
        $this->makeItem($imf);

        $html = $this->printedImf($imf);
        $day  = now()->subMonths(4)->startOfDay();

        $this->assertStringContainsString('Approved: <span>' . $day->format('F d, Y') . '</span>', $html);
        $this->assertStringNotContainsString($day->format('F d, Y h:i A'), $html);
    }

    /** Every review and action is printed with who did it and when. */
    public function test_printed_imf_carries_the_review_trail()
    {
        $imf  = $this->makeImf();
        $item = $this->makeItem($imf);

        $planner = User::find(self::PLANNER_ID);
        $this->actingAs($planner)
            ->post(route('imf.action', $imf->id), [
                'action' => 'approve',
                'type'   => 'new',
                'lines'  => [$item->id => ['planner_remarks' => 'Looks good.']],
            ]);

        $html = $this->printedImf($imf);

        $this->assertStringContainsString('REVIEW AND ACTION TRAIL', $html);
        $this->assertStringContainsString('Acted by', $html);
        $this->assertStringContainsString(e($planner->name), $html);
        $this->assertStringContainsString(
            e('Reviewed by the MCD Planner and endorsed to the MCD Verifier'),
            $html
        );
    }
}
