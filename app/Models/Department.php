<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Ecommerce\SalesHeader;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'created_at', 'updated_at',
        'wfs_approver_id', 'wfs_alternate_approver_id',
        'wfs_approver2_id', 'wfs_alternate_approver2_id',
    ];

    /**
     * The four approver fields, keyed by column => label (Admin > Departments).
     * 1st approver = WFS template step 0 (MANAGER), MRS and IMF.
     * 2nd approver = WFS template step 1 (DIVISION MANAGER), MRS only.
     */
    const WFS_APPROVER_FIELDS = [
        'wfs_approver_id'            => '1st Approver',
        'wfs_alternate_approver_id'  => '1st Alternate Approver',
        'wfs_approver2_id'           => '2nd Approver (MRS)',
        'wfs_alternate_approver2_id' => '2nd Alternate Approver (MRS)',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'department_id');
    }

    /**
     * The approvers handed to api/wfs-api.php as $data['approvers'], so WFS
     * submission uses what is set here instead of searching WFS users.
     *
     * @return array
     */
    public function wfsApprovers()
    {
        return [
            'first'  => [
                'main'      => $this->wfs_approver_id ? (int) $this->wfs_approver_id : null,
                'alternate' => $this->wfs_alternate_approver_id ? (int) $this->wfs_alternate_approver_id : null,
            ],
            'second' => [
                'main'      => $this->wfs_approver2_id ? (int) $this->wfs_approver2_id : null,
                'alternate' => $this->wfs_alternate_approver2_id ? (int) $this->wfs_alternate_approver2_id : null,
            ],
        ];
    }

    /**
     * Why a request of $type ('mrs' or 'imf') from this department can't be sent
     * to WFS yet, or null if it can. MRS needs the 1st and 2nd approvers (two WFS
     * steps); IMF only the 1st. Only checks that they are set — wfs-api.php also
     * checks they are still active in WFS.
     *
     * @param  string  $type
     * @return string|null
     */
    public function wfsApproverGap($type)
    {
        $missing = [];
        if (!$this->wfs_approver_id) {
            $missing[] = '1st approver';
        }
        if (strtolower($type) === 'mrs' && !$this->wfs_approver2_id) {
            $missing[] = '2nd approver';
        }
        if (!$missing) {
            return null;
        }

        return 'Your department (' . $this->name . ') has no WFS ' . implode(' or ', $missing)
            . ' set yet, so ' . (strtolower($type) === 'mrs' ? 'an MRS' : 'an IMF') . ' can\'t be submitted for approval. '
            . 'Please ask the IMP administrator to set it in Admin > Departments.';
    }

    public function orders()
    {
        return $this->hasManyThrough(SalesHeader::class, User::class, 'department_id', 'user_id', 'id', 'id');
    }

    /**
     * The departments table is legacy data with the same department typed many
     * ways ("General Services" / "GENERAL SERVICES", two "Materials Control"
     * rows...). Filter dropdowns only want the departments someone has actually
     * posted an MRS from, one entry per spelling, so this is that list.
     *
     * @return \Illuminate\Support\Collection
     */
    public static function inUse()
    {
        return static::whereIn('id', function ($query) {
                $query->select('users.department_id')
                    ->from('users')
                    ->join('ecommerce_sales_headers', 'ecommerce_sales_headers.user_id', '=', 'users.id')
                    ->whereNotNull('users.department_id');
            })
            ->orderBy('name')
            ->get()
            ->unique(function ($department) {
                return static::normalizeName($department->name);
            })
            ->values();
    }

    /**
     * Every department row spelled like $name, ignoring case and stray spaces,
     * so picking "Materials Control" in a filter also catches its twin row.
     *
     * @param  string  $name
     * @return array
     */
    public static function idsNamed($name)
    {
        $key = static::normalizeName($name);

        return static::all(['id', 'name'])
            ->filter(function ($department) use ($key) {
                return static::normalizeName($department->name) === $key;
            })
            ->pluck('id')
            ->all();
    }

    public static function normalizeName($name)
    {
        return strtoupper(preg_replace('/\s+/', ' ', trim((string) $name)));
    }
}
