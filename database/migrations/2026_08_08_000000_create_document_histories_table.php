<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Audit trail for IMF, MRS, PA-DP and PA-SR.
 *
 * One row per recorded change: who did it, when, the status it moved from/to,
 * the remark they typed and a JSON diff of the fields that actually changed.
 * Rows are written by App\Models\Concerns\RecordsDocumentHistory (model events)
 * and by explicit App\Services\History calls from the workflow controllers.
 */
class CreateDocumentHistoriesTable extends Migration
{
    /**
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('document_histories')) {
            return;
        }

        Schema::create('document_histories', function (Blueprint $table) {
            $table->increments('id');

            // 'MRS' or 'PA'. pa_type further splits PA into 'DP' (raised from an
            // MRS, ends in delegation) and 'SR' (stand-alone supply request).
            $table->string('document_type', 10);
            $table->unsignedInteger('document_id');
            $table->string('document_number', 60)->nullable();
            $table->string('pa_type', 4)->nullable();

            // Machine slug ('created', 'status', 'held', 'revised', 'item_added', ...)
            // plus the two wordings: internal for MCD/Admin, plain for the requestor.
            $table->string('action', 40)->default('status');
            $table->string('title', 190);
            $table->string('requestor_title', 190)->nullable();
            $table->text('description')->nullable();

            $table->string('status_from', 190)->nullable();
            $table->string('status_to', 190)->nullable();
            $table->text('remarks')->nullable();

            // JSON: [{ field, label, old, new }, ...]
            $table->text('changes')->nullable();
            $table->unsignedInteger('revision')->default(0);

            // Actor name/role are snapshotted so the trail survives renames and
            // role changes; actor_id stays for linking back to the user.
            $table->unsignedInteger('actor_id')->nullable();
            $table->string('actor_name', 190)->nullable();
            $table->string('actor_role', 120)->nullable();

            // Department users only see rows flagged visible; purely internal
            // MCD chatter (e.g. verifier notes to the planner) stays hidden.
            $table->boolean('visible_to_requestor')->default(true);

            $table->timestamp('created_at')->nullable();

            $table->index(['document_type', 'document_id'], 'document_histories_doc_index');
            $table->index('created_at', 'document_histories_created_index');
        });

        $this->backfill();
    }

    /**
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('document_histories');
    }

    /**
     * Seed the trail for requests that already exist, using the milestone stamps
     * those records already carry. Without this every historical MRS/PA would
     * show an empty timeline. Only the stamps we can trust are replayed — there
     * is no way to recover the remarks or field diffs of past edits.
     *
     * @return void
     */
    protected function backfill()
    {
        $rows = [];

        if (Schema::hasTable('ecommerce_sales_headers')) {
            $headers = DB::table('ecommerce_sales_headers')
                ->select('id', 'order_number', 'status', 'created_at', 'planner_at', 'planner_by', 'verified_at', 'approved_at', 'received_at', 'received_by', 'user_id', 'revision', 'revised_at')
                ->get();

            foreach ($headers as $h) {
                $rows[] = $this->row('MRS', $h->id, $h->order_number, null, 'created', 'Request created', 'Request submitted', $h->created_at, $h->user_id);

                if (!empty($h->planner_at)) {
                    $rows[] = $this->row('MRS', $h->id, $h->order_number, null, 'planned', 'Reviewed by MCD Planner', 'Reviewed by MCD Planner', $h->planner_at, null, $h->planner_by);
                }
                if (!empty($h->verified_at)) {
                    $rows[] = $this->row('MRS', $h->id, $h->order_number, null, 'verified', 'Verified by MCD Verifier', 'Passed MCD verification', $h->verified_at);
                }
                if (!empty($h->approved_at)) {
                    $rows[] = $this->row('MRS', $h->id, $h->order_number, null, 'approved', 'Approved by MCD Approver', 'Approved by MCD Manager', $h->approved_at);
                }
                if (!empty($h->received_at)) {
                    $rows[] = $this->row('MRS', $h->id, $h->order_number, null, 'received', 'Received for canvass', 'Received by the canvasser', $h->received_at, $h->received_by);
                }
                if (!empty($h->revised_at) && (int) $h->revision > 0) {
                    $rows[] = $this->row('MRS', $h->id, $h->order_number, null, 'revised', 'Revised (Rev' . (int) $h->revision . ')', 'Revised (Rev' . (int) $h->revision . ')', $h->revised_at, null, null, (int) $h->revision);
                }
            }
        }

        if (Schema::hasTable('purchase_advice')) {
            $advices = DB::table('purchase_advice as pa')
                ->leftJoin('ecommerce_sales_headers as mrs', 'mrs.id', '=', 'pa.mrs_id')
                ->select('pa.id', 'pa.pa_number', 'pa.created_at', 'pa.created_by', 'pa.verified_at', 'pa.verified_by', 'pa.approved_at', 'pa.approved_by', 'pa.received_at', 'pa.received_by', 'pa.revision', 'pa.revised_at', 'mrs.order_number as mrs_number')
                ->get();

            foreach ($advices as $p) {
                $paType = !empty($p->mrs_number) ? 'DP' : 'SR';

                $rows[] = $this->row('PA', $p->id, $p->pa_number, $paType, 'created', 'Purchase advice created', 'Purchase advice prepared', $p->created_at, $p->created_by);

                if (!empty($p->verified_at)) {
                    $rows[] = $this->row('PA', $p->id, $p->pa_number, $paType, 'verified', 'Verified by MCD Verifier', 'Passed MCD verification', $p->verified_at, $p->verified_by);
                }
                if (!empty($p->approved_at)) {
                    $rows[] = $this->row('PA', $p->id, $p->pa_number, $paType, 'approved', 'Approved by MCD Approver', 'Approved by MCD Manager', $p->approved_at, $p->approved_by);
                }
                if (!empty($p->received_at)) {
                    $rows[] = $this->row('PA', $p->id, $p->pa_number, $paType, 'received', 'Received for canvass', 'Received by the canvasser', $p->received_at, $p->received_by);
                }
                if (!empty($p->revised_at) && (int) $p->revision > 0) {
                    $rows[] = $this->row('PA', $p->id, $p->pa_number, $paType, 'revised', 'Revised (Rev' . (int) $p->revision . ')', 'Revised (Rev' . (int) $p->revision . ')', $p->revised_at, null, null, (int) $p->revision);
                }
            }
        }

        if (Schema::hasTable('inventory_requests')) {
            $imfs = DB::table('inventory_requests')
                ->select('id', 'status', 'created_at', 'submitted_at', 'approved_at', 'user_id', 'revision', 'revised_at')
                ->get();

            foreach ($imfs as $i) {
                $rows[] = $this->row('IMF', $i->id, 'IMF-' . $i->id, null, 'created', 'IMF raised', 'Request created', $i->created_at, $i->user_id);

                if (!empty($i->submitted_at)) {
                    $rows[] = $this->row('IMF', $i->id, 'IMF-' . $i->id, null, 'submitted', 'Submitted for WFS approval', 'Submitted - for WFS approval', $i->submitted_at, $i->user_id);
                }
                if (!empty($i->approved_at)) {
                    $rows[] = $this->row('IMF', $i->id, 'IMF-' . $i->id, null, 'approved', 'Approved', 'Approved', $i->approved_at);
                }
                if (!empty($i->revised_at) && (int) $i->revision > 0) {
                    $rows[] = $this->row('IMF', $i->id, 'IMF-' . $i->id, null, 'revised', 'Revised (Rev' . (int) $i->revision . ')', 'Revised (Rev' . (int) $i->revision . ')', $i->revised_at, null, null, (int) $i->revision);
                }
            }
        }

        // 18 bound parameters per row, against SQL Server's hard 2100-parameter
        // limit per statement — 100 rows a batch leaves comfortable headroom.
        foreach (array_chunk($rows, 100) as $chunk) {
            DB::table('document_histories')->insert($chunk);
        }
    }

    /**
     * Build one backfilled row. $actorName is used when the source column stores
     * a name rather than a user id (ecommerce_sales_headers.planner_by).
     *
     * @return array
     */
    protected function row($type, $id, $number, $paType, $action, $title, $requestorTitle, $at, $actorId = null, $actorName = null, $revision = 0)
    {
        return [
            'document_type'        => $type,
            'document_id'          => $id,
            'document_number'      => $number,
            'pa_type'              => $paType,
            'action'               => $action,
            'title'                => $title,
            'requestor_title'      => $requestorTitle,
            'description'          => 'Reconstructed from the record\'s saved timestamps.',
            'status_from'          => null,
            'status_to'            => null,
            'remarks'              => null,
            'changes'              => null,
            'revision'             => $revision,
            'actor_id'             => is_numeric($actorId) ? (int) $actorId : null,
            'actor_name'           => $actorName,
            'actor_role'           => null,
            'visible_to_requestor' => 1,
            'created_at'           => $at,
        ];
    }
}
