<?php

namespace App\Services;

use App\Models\Department;
use App\Models\User;
use App\Models\Ecommerce\PurchaseAdviceDetail;
use App\Models\Ecommerce\SalesDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Unprocessed PA report — every PA sitting with Purchasing for canvass that
 * still has quantity left to order. This is what the Purchasing Officer pulls
 * on the 15th and 30th, without the fully-ordered and cancelled PAs the old
 * Manage PA export made them strip out by hand.
 *
 * Both kinds of PA are covered, told apart the same way the PA listing tabs do:
 *  - DP: raised off a numbered MRS. The MRS header carries the purchasing
 *    status and receipt; balance is the MRS lines, hold lines (promo_id = 1)
 *    left out — same as SalesHeader::getBalanceToOrder() on the Manage PA list.
 *  - SR: a stand-alone stock-replenishment PA. The PA row carries the status
 *    and receipt; balance is its purchase_advice_details, held lines left out.
 */
class UnprocessedPaReport
{
    const RECEIVED = 'RECEIVED FOR CANVASS (Purchasing Officer)';

    /** Aging buckets in whole days since Purchasing received the PA. */
    const AGING_BUCKETS = [
        '0-7'   => [0, 7],
        '8-14'  => [8, 14],
        '15-30' => [15, 30],
        '31+'   => [31, null],
    ];

    /** @var array */
    public $filters;

    /** @var \Carbon\Carbon */
    public $now;

    public function __construct(array $filters = [], Carbon $now = null)
    {
        $this->now = $now ?: Carbon::now();
        $this->filters = [
            'search'       => trim((string) ($filters['search'] ?? '')),
            'type'         => in_array($filters['type'] ?? '', ['DP', 'SR'], true) ? $filters['type'] : '',
            'department'   => trim((string) ($filters['department'] ?? '')),
            'purchaser'    => (int) ($filters['purchaser'] ?? 0),
            'aging'        => array_key_exists($filters['aging'] ?? '', self::AGING_BUCKETS) ? $filters['aging'] : '',
            'startdate'    => $this->dateOrEmpty($filters['startdate'] ?? ''),
            'enddate'      => $this->dateOrEmpty($filters['enddate'] ?? ''),
            'include_zero' => !empty($filters['include_zero']),
        ];
    }

    public static function fromRequest(Request $request)
    {
        return new static($request->only([
            'search', 'type', 'department', 'purchaser', 'aging', 'startdate', 'enddate', 'include_zero',
        ]));
    }

    /**
     * One row per PA with the filters applied: pa_type, pa_id, mrs_id,
     * order_number, pa_number, posted_at, received_at, received_by,
     * department_id, department, is_hold, balance, aging_days.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function query()
    {
        $query = $this->unfiltered();
        $f = $this->filters;

        if (!$f['include_zero']) {
            $query->where('x.balance', '>', 0);
        }
        if ($f['type'] !== '') {
            $query->where('x.pa_type', $f['type']);
        }
        if ($f['department'] !== '') {
            // SR PAs have no requesting department, so picking one leaves them out.
            $query->whereIn('x.department_id', Department::idsNamed($f['department']) ?: [0]);
        }
        if ($f['purchaser'] > 0) {
            $query->where('x.received_by', $f['purchaser']);
        }
        if ($f['aging'] !== '') {
            list($from, $to) = self::AGING_BUCKETS[$f['aging']];
            $query->where('x.aging_days', '>=', $from);
            if ($to !== null) {
                $query->where('x.aging_days', '<=', $to);
            }
        }
        if ($f['startdate'] !== '') {
            $query->where('x.posted_at', '>=', $f['startdate']);
        }
        if ($f['enddate'] !== '') {
            $query->where('x.posted_at', '<=', $f['enddate'] . ' 23:59:59');
        }
        if ($f['search'] !== '') {
            $search = '%' . $f['search'] . '%';
            $query->where(function ($q) use ($search) {
                $q->where('x.order_number', 'like', $search)
                    ->orWhere('x.pa_number', 'like', $search);
            });
        }

        return $query;
    }

    /** Oldest first — that's the order the report is read in. */
    public function ordered()
    {
        return $this->query()
            ->orderByRaw('CASE WHEN x.aging_days IS NULL THEN 1 ELSE 0 END')
            ->orderBy('x.aging_days', 'desc')
            ->orderBy('x.pa_number');
    }

    /**
     * Headline numbers for the filtered set: PA count, total balance qty and a
     * count per aging bucket.
     *
     * @return array
     */
    public function summary()
    {
        $selects = ['COUNT(*) AS total', 'COALESCE(SUM(x.balance), 0) AS balance'];
        $i = 0;
        foreach (self::AGING_BUCKETS as $label => $range) {
            $cond = 'x.aging_days >= ' . (int) $range[0] . ($range[1] !== null ? ' AND x.aging_days <= ' . (int) $range[1] : '');
            $selects[] = "SUM(CASE WHEN {$cond} THEN 1 ELSE 0 END) AS bucket{$i}";
            $i++;
        }

        $row = DB::query()->fromSub($this->query(), 's')
            ->selectRaw(str_replace('x.', 's.', implode(', ', $selects)))
            ->first();

        $buckets = [];
        $i = 0;
        foreach (array_keys(self::AGING_BUCKETS) as $label) {
            $buckets[$label] = (int) $row->{'bucket' . $i};
            $i++;
        }

        return [
            'total'   => (int) $row->total,
            'balance' => (float) $row->balance,
            'buckets' => $buckets,
        ];
    }

    /**
     * Purchasers who currently hold at least one PA at canvass, for the filter.
     *
     * @return \Illuminate\Support\Collection
     */
    public function purchasers()
    {
        $ids = $this->unfiltered()->whereNotNull('x.received_by')->distinct()->pluck('x.received_by');

        return User::whereIn('id', $ids)->orderBy('name')->get(['id', 'name']);
    }

    /**
     * The PA rows plus their item lines, for the Excel export. Lines already
     * fully ordered, and held lines, are left out unless include_zero is on.
     *
     * @return array  [ ['row' => stdClass, 'lines' => [ [..line fields..], ... ]], ... ]
     */
    public function withLines()
    {
        $rows = $this->ordered()->get();

        $mrsIds = $rows->where('pa_type', 'DP')->pluck('mrs_id')->filter()->unique()->values()->all();
        $srIds  = $rows->where('pa_type', 'SR')->pluck('pa_id')->filter()->unique()->values()->all();

        $dpLines = collect();
        foreach (array_chunk($mrsIds, 1000) as $chunk) {
            $dpLines = $dpLines->merge(SalesDetail::with('product')->whereIn('sales_header_id', $chunk)->orderBy('id')->get());
        }
        $dpLines = $dpLines->groupBy('sales_header_id');

        $srLines = collect();
        foreach (array_chunk($srIds, 1000) as $chunk) {
            $srLines = $srLines->merge(PurchaseAdviceDetail::with('product')->whereIn('purchase_advice_id', $chunk)->orderBy('id')->get());
        }
        $srLines = $srLines->groupBy('purchase_advice_id');

        $out = [];
        foreach ($rows as $row) {
            $source = $row->pa_type === 'DP'
                ? $dpLines->get($row->mrs_id, collect())
                : $srLines->get($row->pa_id, collect());

            $lines = [];
            foreach ($source as $item) {
                $line = $row->pa_type === 'DP' ? $this->dpLine($item) : $this->srLine($item);
                if (!$this->filters['include_zero'] && $line['status'] !== 'Open') {
                    continue;
                }
                $lines[] = $line;
            }
            $out[] = ['row' => $row, 'lines' => $lines];
        }

        return $out;
    }

    /** User names for the received_by ids on a page of rows. */
    public static function namesFor($rows)
    {
        $ids = collect($rows)->pluck('received_by')->filter()->unique()->values()->all();

        return $ids ? User::whereIn('id', $ids)->pluck('name', 'id') : collect();
    }

    /**
     * Both PA kinds as one derived table x, aging worked out against $this->now
     * in whole days (same as the Carbon diffInDays the Manage PA list shows).
     */
    protected function unfiltered()
    {
        $dpBalance = '(SELECT COALESCE(SUM(CASE WHEN COALESCE(d.promo_id, 0) = 1 THEN 0'
            . ' ELSE COALESCE(d.qty_to_order, 0) - COALESCE(d.qty_ordered, 0) END), 0)'
            . ' FROM ecommerce_sales_details d WHERE d.sales_header_id = h.id AND d.deleted_at IS NULL)';

        $dp = DB::table('ecommerce_sales_headers as h')
            ->leftJoin('purchase_advice as pa', 'pa.mrs_id', '=', 'h.id')
            ->leftJoin('users as u', 'u.id', '=', 'h.user_id')
            ->leftJoin('departments as dep', 'dep.id', '=', 'u.department_id')
            ->whereNull('h.deleted_at')
            ->where('h.status', self::RECEIVED)
            ->where('h.for_pa', 1)
            ->where('h.is_pa', 1)
            // A PA cancelled by MCD leaves the MRS reading RECEIVED FOR CANVASS.
            ->where(function ($q) {
                $q->whereNull('pa.status')->orWhere('pa.status', 'not like', '%CANCEL%');
            })
            ->selectRaw("'DP' AS pa_type, pa.id AS pa_id, h.id AS mrs_id, h.order_number, pa.pa_number,"
                . ' h.created_at AS posted_at, h.received_at, h.received_by, u.department_id, dep.name AS department,'
                . " COALESCE(pa.is_hold, 0) AS is_hold, {$dpBalance} AS balance");

        $srBalance = '(SELECT COALESCE(SUM(CASE WHEN COALESCE(pd.is_hold, 0) = 1 THEN 0'
            . ' ELSE COALESCE(pd.qty_to_order, 0) - COALESCE(pd.qty_ordered, 0) END), 0)'
            . ' FROM purchase_advice_details pd WHERE pd.purchase_advice_id = pa.id)';

        $sr = DB::table('purchase_advice as pa')
            ->leftJoin('ecommerce_sales_headers as h', function ($join) {
                $join->on('h.id', '=', 'pa.mrs_id')->whereNull('h.deleted_at');
            })
            ->where('pa.status', self::RECEIVED)
            ->where(function ($q) {
                $q->whereNull('h.id')->orWhereNull('h.order_number')->orWhere('h.order_number', '');
            })
            ->selectRaw("'SR' AS pa_type, pa.id AS pa_id, NULL AS mrs_id, NULL AS order_number, pa.pa_number,"
                . ' pa.created_at AS posted_at, pa.received_at, pa.received_by, NULL AS department_id, NULL AS department,'
                . " COALESCE(pa.is_hold, 0) AS is_hold, {$srBalance} AS balance");

        $withAging = DB::query()
            ->fromSub($dp->unionAll($sr), 'r')
            ->selectRaw('r.*, CASE WHEN r.received_at IS NULL THEN NULL'
                . ' ELSE DATEDIFF(SECOND, r.received_at, ?) / 86400 END AS aging_days', [$this->now->format('Y-m-d H:i:s')]);

        return DB::query()->fromSub($withAging, 'x');
    }

    protected function dpLine($item)
    {
        $balance = (float) $item->qty_to_order - (float) $item->qty_ordered;

        return [
            'code'         => optional($item->product)->code,
            'description'  => optional($item->product)->name ?: $item->product_name,
            'oem'          => optional($item->product)->oem,
            'uom'          => optional($item->product)->uom ?: $item->uom,
            'frequency'    => $item->frequency,
            'par_to'       => $item->par_to,
            'previous_po'  => $item->previous_mrs,
            'current_po'   => $item->po_no,
            'po_released'  => $item->po_date_released,
            'qty_to_order' => (float) $item->qty_to_order,
            'qty_ordered'  => (float) $item->qty_ordered,
            'balance'      => $balance,
            'status'       => (int) $item->promo_id === 1 ? 'On hold' : ($balance > 0 ? 'Open' : 'Fully ordered'),
        ];
    }

    protected function srLine($item)
    {
        $balance = (float) $item->qty_to_order - (float) $item->qty_ordered;

        return [
            'code'         => optional($item->product)->code,
            'description'  => optional($item->product)->name,
            'oem'          => optional($item->product)->oem,
            'uom'          => optional($item->product)->uom,
            'frequency'    => $item->frequency,
            'par_to'       => $item->par_to,
            'previous_po'  => $item->previous_po,
            'current_po'   => $item->current_po,
            'po_released'  => $item->po_date_released,
            'qty_to_order' => (float) $item->qty_to_order,
            'qty_ordered'  => (float) $item->qty_ordered,
            'balance'      => $balance,
            'status'       => (int) $item->is_hold === 1 ? 'On hold' : ($balance > 0 ? 'Open' : 'Fully ordered'),
        ];
    }

    protected function dateOrEmpty($value)
    {
        $value = trim((string) $value);

        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) ? $value : '';
    }
}
