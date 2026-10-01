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
        'name', 'created_at', 'updated_at'
    ];

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
