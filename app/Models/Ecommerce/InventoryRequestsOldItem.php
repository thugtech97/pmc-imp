<?php

namespace App\Models\Ecommerce;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * The "before" values of an item on an Update IMF.
 *
 * Since 2026-10-16 every item has its own row (item_id) holding the item's
 * full values at the time it was added to the IMF. Older rows have no item_id
 * and hold only the fields the requestor changed — those IMFs had one item.
 */
class InventoryRequestsOldItem extends Model
{
    use HasFactory;

    protected $table = 'inventory_requests_old_items';

    protected $fillable = [
        'imf_no',
        'item_id',
        'stock_code',
        'item_description',
        'brand',
        'OEM_ID',
        'UoM',
        'usage_rate_qty',
        'usage_frequency',
        'purpose',
        'min_qty',
        'max_qty',
        'updated_at',
    ];

    /** [label, column] of every field an Update IMF can change, in display order. */
    const FIELDS = [
        ['Item Description', 'item_description'],
        ['Brand',            'brand'],
        ['OEM ID',           'OEM_ID'],
        ['UoM',              'UoM'],
        ['Usage Rate Qty',   'usage_rate_qty'],
        ['Usage Frequency',  'usage_frequency'],
        ['Min Qty',          'min_qty'],
        ['Max Qty',          'max_qty'],
        ['Purpose',          'purpose'],
    ];

    /**
     * The old-values row of each item of an IMF, keyed by item id.
     *
     * @param  int  $imfNo
     * @param  \Illuminate\Support\Collection  $items
     * @return array
     */
    public static function forItems($imfNo, $items)
    {
        $rows = self::where('imf_no', $imfNo)->orderBy('id')->get();
        $map = [];

        foreach ($rows as $row) {
            if ($row->item_id) {
                $map[$row->item_id] = $row;
            }
        }

        // Pre-2026-10-16 row: the IMF had a single item.
        $legacy = $rows->first(function ($row) {
            return !$row->item_id;
        });
        $first = $items->first();

        if ($legacy && $first && !isset($map[$first->id])) {
            $map[$first->id] = $legacy;
        }

        return $map;
    }

    /**
     * Field-by-field comparison of an item against its old values.
     *
     * Each row: label, field, old (the value before), new (the requested value)
     * and changed. With no old-values row at all, nothing counts as changed and
     * old is null.
     *
     * @param  self|null  $old
     * @param  \App\Models\Ecommerce\InventoryRequestItems|null  $item
     * @return array
     */
    public static function diff($old, $item)
    {
        $rows = [];

        foreach (self::FIELDS as $pair) {
            $label = $pair[0];
            $field = $pair[1];
            $new = $item ? $item->{$field} : null;
            $before = $old ? $old->{$field} : null;

            if (!$old) {
                $changed = false;
            } elseif (!$old->item_id) {
                // Legacy rows kept only the fields that changed.
                $changed = trim((string) $before) !== '';
                if (!$changed) {
                    $before = $new;
                }
            } else {
                $changed = !self::sameValue($before, $new);
            }

            $rows[] = [
                'label'   => $label,
                'field'   => $field,
                'old'     => $before,
                'new'     => $new,
                'changed' => $changed,
            ];
        }

        return $rows;
    }

    /**
     * "10" and "10.00" are the same quantity; text is compared as typed.
     */
    private static function sameValue($a, $b)
    {
        $a = trim((string) $a);
        $b = trim((string) $b);

        if (is_numeric($a) && is_numeric($b)) {
            return (float) $a === (float) $b;
        }

        return $a === $b;
    }
}
