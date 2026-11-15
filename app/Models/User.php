<?php

namespace App\Models;

use App\Models\Role;
use App\Models\Department;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\CausesActivity;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Notifications\UserResetPasswordNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use App\Notifications\NewUserResetPasswordNotification;

use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Notifications\SendEmailNotificationReorderPoint;
use App\Notifications\SendEmailNotificationCustomerWishlist;

class User extends Authenticatable implements MustVerifyEmail
{
    use Notifiable;
    use SoftDeletes;
    use CausesActivity;
    use HasFactory;

    /** Department users — no row in the role table, so this id is fixed. See is_department_user(). */
    const DEPT_USER_ROLE_ID = 6;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'email_verified_at', 'password', 'role_id', 'is_active', 'remember_token', 'firstname', 'middlename', 'lastname', 'avatar', 'user_id', 'isDeleted','mobile','phone','address_street','address_city','address_municipality','address_zip','department_id'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function getFullNameAttribute()
    {
        return "$this->firstname $this->lastname";
    }

    public function getFirstNameAttribute($value)
    {
        return ucfirst($value);
    }

    public function getLastNameAttribute($value)
    {
        return ucfirst($value);
    }

    public function getAddressAttribute()
    {
        return $this->address_street.", ".$this->address_municipality.", ".$this->address_city.", ".$this->address_zip;
    }

    public function getRoleAttribute($value)
    {
        return strtoupper($value);
    }

    public function role_name()
    {
        return User::userRole($this->role_id);
    }

    public static function totalUser()
    {
        $total = User::where('is_active','=',1)->count();

        return $total;
    }

    public static function activeTotalUser()
    {
        // $total = User::where('role_id','<>',self::DEPT_USER_ROLE_ID)->where('is_active','=',1)->whereNotNull('email_verified_at')->count();
        $total = User::where('role_id','<>',self::DEPT_USER_ROLE_ID)->where('is_active','=',1)->count();

        return $total;
    }

    public static function inactiveTotalUser()
    {
        $total = User::where('role_id','<>',self::DEPT_USER_ROLE_ID)->onlyTrashed()->count();

        return $total;
    }

    public static function userEmail($id)
    {
        $data = User::where('id',$id)->first();

        return $data->email;
    }

    public static function userRole($id)
    {
        $data = Role::where('id',$id)->first();

        if (!$data) {
            return '';
        }

        return $data->name;
    }

    public function send_reset_password_email()
    {
        $token = app('auth.password.broker')->createToken($this);

        // $this->notify(new UserResetPasswordNotification($token));
    }

    public function send_reset_temporary_password_email()
    {
        $token = app('auth.password.broker')->createToken($this);
        logger($token);
        // $this->notify(new NewUserResetPasswordNotification($token));
    }

    public function send_email_notification_on_customer_wishlist($product)
    {
        // $this->notify(new SendEmailNotificationCustomerWishlist($product));
    }


    public function reorder_point_email_notification()
    {
        // $this->notify(new SendEmailNotificationReorderPoint());
    }

    public function has_access_to_pages_module()
    {
        return $this->has_access_to_module(array_keys(Permission::modules())[0]);
    }

    public function has_access_to_albums_module()
    {
        return $this->has_access_to_module(array_keys(Permission::modules())[1]);
    }

    public function has_access_to_file_manager_module()
    {
        return $this->has_access_to_module(array_keys(Permission::modules())[2]);
    }

    public function has_access_to_menu_module()
    {
        return $this->has_access_to_module(array_keys(Permission::modules())[3]);
    }

    public function has_access_to_news_module()
    {
        return $this->has_access_to_module(array_keys(Permission::modules())[4]);
    }

    public function has_access_to_news_categories_module()
    {
        return $this->has_access_to_module(array_keys(Permission::modules())[5]);
    }

    public function has_access_to_website_settings_module()
    {
        return $this->has_access_to_module(array_keys(Permission::modules())[6]);
    }

    public function has_access_to_audit_logs_module()
    {
        return $this->has_access_to_module(array_keys(Permission::modules())[7]);
    }

    public function has_access_to_user_module()
    {
        return $this->has_access_to_module(array_keys(Permission::modules())[8]);
    }

    public function has_access_to_module($module)
    {
        if ($this->is_an_admin() == 1) {
            return true;
        }

        $routes = $this->get_module_routes($module);

        foreach($routes as $route) {
            if ($this->is_route_exist_to_user_permission($route)) {
                return true;
                break;
            }
        }

        return false;
    }

    private function get_module_routes($module)
    {
        return Permission::where('module', $module)->pluck('name');
    }

    private function is_route_exist_to_user_permission($route)
    {
        return \App\Models\ViewPermissions::check_permission($this->role_id, $route) == 1;
    }

    public function has_access_to_route($route)
    {
        if ($this->is_an_admin()) {
            return true;
        }

        $userPermissionRoutes = $this->get_assigned_routes();

        if (in_array($route, $userPermissionRoutes)) {
            return true;
        }

        return false;
    }

    public function get_assigned_routes()
    {
        $permission = $this->assign_role->permissions;

        if ($permission) {
            return $permission->pluck('routes')->flatten()->all();
        }

        return [];
    }

    public function get_image_url_storage_path()
    {
        $delimiter = 'storage/';
        if (strpos($this->avatar, $delimiter) !== false) {
            $paths = explode($delimiter, $this->avatar);
            return $paths[1];
        }

        return '';
    }

    public function get_image_file_name()
    {
        $path = explode('/', $this->avatar);
        $nameIndex = count($path) - 1;
        if ($nameIndex < 0)
            return '';

        return $path[$nameIndex];
    }

    public function is_an_admin()
    {
        return $this->has_role('Admin');
    }

    public function is_not_an_admin()
    {
        return !$this->is_an_admin();
    }

    /**
     * Department users have no row in the role table — role_id 6 is hard-wired
     * (see the "Department User" option on the user form), so it can't be matched
     * by name. Use this instead of comparing role_id to 6.
     */
    public function is_department_user()
    {
        return (int) $this->role_id === self::DEPT_USER_ROLE_ID;
    }

    /**
     * Why this user can't submit a request of $type ('mrs' or 'imf') to WFS, or
     * null if they can — their department needs its WFS approvers set
     * (Admin > Departments). Used by the submit actions and to disable the
     * submit buttons with this message.
     *
     * @param  string  $type
     * @return string|null
     */
    public function wfsSubmitBlocker($type)
    {
        $department = $this->department;
        if (!$department) {
            return 'Your account has no department, so requests can\'t be submitted for approval. Please ask the IMP administrator to set your department.';
        }

        return $department->wfsApproverGap($type);
    }

    /**
     * Users whose role name is any of the given names.
     *   User::withRole('Purchaser')->get()
     */
    public function scopeWithRole($query, ...$names)
    {
        return $query->whereHas('assign_role', function ($q) use ($names) {
            $q->whereIn('name', $names);
        });
    }

    public function assign_role()
    {
        return $this->belongsTo(Role::class,'role_id', 'id');
    }

    /**
     * True when the user's role name is any of the given names (case/space-insensitive).
     * Use this instead of role_id checks — role ids differ per environment.
     *   $user->has_role('Admin', 'MCD Planner')
     */
    public function has_role(...$names)
    {
        $current = strtolower(trim((string) optional($this->assign_role)->name));
        if ($current === '') {
            return false;
        }

        foreach ($names as $name) {
            if (strtolower(trim($name)) === $current) {
                return true;
            }
        }

        return false;
    }




    // ******** AUDIT LOG ******** //
    // Need to change every model
    static $oldModel;
    static $tableTitle = 'user';
    static $name = 'name';
    static $unrelatedFields = ['id', 'email_verified_at', 'created_at', 'updated_at', 'deleted_at'];
    static $logName = [
        'name' => 'name',
        'email' => 'email',
        'firstname' => 'firstname',
        'lastname' => 'lastname',
        'avatar' => 'avatar',
        'role_id' => 'role',
        'is_active' => 'status',
        'user_id' => 'user id'

    ];
    // END Need to change every model

    public static function boot()
    {
        parent::boot();

        self::created(function($model) {
            $name = $model[self::$name];

            if(self::$logName['role_id'] <> self::DEPT_USER_ROLE_ID){
                ActivityLog::create([
                    'log_by' => auth()->id(),
                    'activity_type' => 'insert',
                    'dashboard_activity' => 'created a new '. self::$tableTitle,
                    'activity_desc' => 'created the '. self::$tableTitle .' '. $name,
                    'activity_date' => date("Y-m-d H:i:s"),
                    'db_table' => $model->getTable(),
                    'old_value' => '',
                    'new_value' => $name,
                    'reference' => $model->id
                ]);
            }
            
        });

        self::updating(function($model) {
            self::$oldModel = $model->fresh();
        });

        self::updated(function($model) {
            $name = $model[self::$name];

            if(self::$logName['role_id'] <> self::DEPT_USER_ROLE_ID){
                $oldModel = self::$oldModel->toArray();
                foreach ($oldModel as $fieldName => $value) {
                    if (in_array($fieldName, self::$unrelatedFields)) {
                        continue;
                    }

                    $oldValue = $model[$fieldName];
                    if ($oldValue != $value) {
                        ActivityLog::create([
                            'log_by' => auth()->id(),
                            'activity_type' => 'update',
                            'dashboard_activity' => 'updated the '. self::$tableTitle .' '. self::$logName[$fieldName],
                            'activity_desc' => 'updated the '. self::$tableTitle .' '. self::$logName[$fieldName] .' of '. $name .' from '. $oldValue .' to '. $value,
                            'activity_date' => date("Y-m-d H:i:s"),
                            'db_table' => $model->getTable(),
                            'old_value' => $oldValue,
                            'new_value' => $value,
                            'reference' => $model->id
                        ]);
                    }
                }
            }
        });

        self::deleted(function($model){
            $name = $model[self::$name];
            if(self::$logName['role_id'] <> self::DEPT_USER_ROLE_ID){
                ActivityLog::create([
                    'log_by' => auth()->id(),
                    'activity_type' => 'delete',
                    'dashboard_activity' => 'deleted a '. self::$tableTitle,
                    'activity_desc' => 'deleted the '. self::$tableTitle .' '. $name,
                    'activity_date' => date("Y-m-d H:i:s"),
                    'db_table' => $model->getTable(),
                    'old_value' => '',
                    'new_value' => '',
                    'reference' => $model->id
                ]);
            }
        });
    }
}
