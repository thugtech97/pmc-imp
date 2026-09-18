<?php

namespace App\Http\Controllers\Settings;

use PDO;
use Auth;
use Exception;
use App\Models\Role;
use App\Models\User;

use App\Http\Requests;
use App\Helpers\Setting;

use App\Models\Department;
use App\Models\Permission;

use App\Models\ActivityLog;
use App\Mail\AddNewUserMail;

use Illuminate\Http\Request;
use App\Exports\BladeViewExport;

use App\Mail\UpdatePasswordMail;
use App\Http\Requests\UserRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;

use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Input;
use Facades\App\Helpers\ListingHelper;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;


class UserController extends Controller
{
    use SendsPasswordResetEmails;

    private $searchFields = ['name'];

    public function __construct()
    {
        Permission::module_init($this, 'user');
    }

    public function index()
    {
        $listing = ListingHelper::required_condition('role_id', '<>', 6);
        $users = $listing->simple_search(User::class, $this->searchFields);

        // Simple search init data
        $filter = $listing->get_filter($this->searchFields);

        $searchType = 'simple_search';

        return view('admin.users.index',compact('users','filter', 'searchType'));
    }

    public function create()
    {
        $departments = Department::all();
        $roles = Role::orderBy('name', 'asc')->get();
        return view('admin.users.create', compact('roles', 'departments'));
    }

    public function store(UserRequest $request)
    {
//        if(User::where('name',$request->fname.' '.$request->lname)->exists()){
//            return back()->with('duplicate', __('standard.users.duplicate_email'));
//        } else {
        $user = User::create([
            'firstname'      => $request->fname,
            'middlename'     => $request->mname,
            'lastname'       => $request->lname,
            'name'           => $request->fname . ' ' . ($request->mname ? strtoupper(substr($request->mname, 0, 1)) . '. ' : '') . $request->lname,
            'password'       => Hash::make('password'),
            'email'          => $request->email,
            'role_id'        => $request->role,
            'is_active'      => 1,
            'user_id'        => Auth::id(),
            'remember_token' => str_random(10),
            'department_id'  => $request->department
        ]);

        //$user->send_reset_temporary_password_email();

        return redirect()->route('users.index')->with('success', 'Pending for activation. Please remind the user to check the email and activate the account.');
//        }
    }

    public function edit($id)
    {
        $roles = Role::orderBy('name','asc')->get();
        $user = User::where('id',$id)->first();

        return view('admin.users.edit',compact('user','roles'));
    }

    public function update(Request $request, User $user)
    {
        Validator::make($request->all(), [
            'fname' => 'required|max:150',
            'lname' => 'required|max:150',
            'email' => 'required|email|max:191|unique:users,email,'.$user->id,
            'role' => 'required|exists:role,id'
        ])->validate();

        $user->update([
            'firstname'      => $request->fname,
            'middlename'     => $request->mname,
            'lastname'       => $request->lname,
            'name'           => $request->fname . ' ' . ($request->mname ? strtoupper(substr($request->mname, 0, 1)) . '. ' : '') . $request->lname,
            'email'    => $request->email,
            'role_id'  => $request->role,
            'user_id'  => Auth::id(),
        ]);

        return redirect()->route('users.edit', $user->id)->with('success', __('standard.users.update_success'));
    }

    public function deactivate(Request $request)
    {
        $user = User::find($request->user_id);

        $user->update([
            'is_active' => 0,
            'user_id'   => Auth::id(),
        ]);
        $user->delete();

        return back()->with('success', __('standard.users.status_success', ['status' => 'deactivated']));
    }

    public function activate(Request $request)
    {
        $user = User::withTrashed()->find($request->user_id);

        $user->update([
            'is_active' => 1,
            'user_id'   => Auth::id(),
        ]);
        $user->restore();

        return back()->with('success', __('standard.users.status_success', ['status' => 'activated']));
    }


    public function show($id, $filter = null)
    {
        $searchFields = ['db_table'];
        $filterFields = ['activity_date', 'db_table'];

        $user = User::withTrashed()->find($id);


        $listing = ListingHelper::required_condition('log_by', '=', $id)->sort_by('activity_date')->filter_fields($filterFields);
        $logs = $listing->simple_search(ActivityLog::class, $searchFields);

        // Simple search init data
        $filter = $listing->get_filter($searchFields);
        $searchType = 'simple_search';

        return view('admin.users.profile',compact('user','logs', 'filter', 'searchType'));
    }

    public function filter(Request $request)
    {
        $params = $request->all();

        return $this->apply_filter($params);
    }

    public function apply_filter($param = null)
    {
        $user = User::where('id',$param['id'])->first();

        if(isset($param['order'])){
            $logs = ActivityLog::where('log_by',$param['id'])->orderBy($param['sort'],$param['order'])->paginate($param['pageLimit']);
        } else {
            $logs = ActivityLog::where('log_by',$param['id'])->paginate($param['pageLimit']);
        }

        return view('admin.users.profile',compact('user','logs','param'));
    }

    public function employee_lookup() {
        return $this->fetchHrisEmployeesRaw();
    }

    /**
     * Server-side search behind the Select2 employee pickers (checkout, MRS
     * edit). Replaces the old "fetch everyone once and park it in
     * localStorage forever" approach: the HRIS list is cached here for a short
     * while and only the rows matching the typed query are sent down, a page
     * at a time.
     */
    public function employee_search(Request $request) {
        $q       = trim((string) $request->input('q', ''));
        $page    = max(1, (int) $request->input('page', 1));
        $perPage = 30;

        $employees = $this->hrisEmployees();

        if ($q !== '') {
            // Every typed word has to appear somewhere in "NAME : DEPT", so
            // "dela cruz eng" narrows the way a search box is expected to.
            $tokens = preg_split('/\s+/', mb_strtolower($q));
            $employees = array_values(array_filter($employees, function ($emp) use ($tokens) {
                $hay = mb_strtolower($emp['fullnamewithdept']);
                foreach ($tokens as $token) {
                    if ($token !== '' && mb_strpos($hay, $token) === false) {
                        return false;
                    }
                }
                return true;
            }));

            // Names that start with the query float to the top.
            $prefix = mb_strtolower($q);
            usort($employees, function ($a, $b) use ($prefix) {
                $aStarts = mb_strpos(mb_strtolower($a['fullnamewithdept']), $prefix) === 0 ? 0 : 1;
                $bStarts = mb_strpos(mb_strtolower($b['fullnamewithdept']), $prefix) === 0 ? 0 : 1;
                if ($aStarts !== $bStarts) {
                    return $aStarts - $bStarts;
                }
                return strcmp($a['fullnamewithdept'], $b['fullnamewithdept']);
            });
        }

        $total = count($employees);
        $slice = array_slice($employees, ($page - 1) * $perPage, $perPage);

        $results = array_map(function ($emp) {
            $parts = explode(':', $emp['fullnamewithdept'], 2);
            return [
                'id'   => $emp['fullnamewithdept'],
                'text' => trim($parts[0]),
                'dept' => isset($parts[1]) ? trim($parts[1]) : '',
            ];
        }, $slice);

        return response()->json([
            'results'    => $results,
            'pagination' => ['more' => ($page * $perPage) < $total],
        ]);
    }

    /**
     * Active employees from HRIS as [['fullnamewithdept' => 'NAME : DEPT', ...], ...].
     * Cached briefly so a burst of keystrokes does not hammer the HRIS servers;
     * an empty/failed fetch is deliberately not cached so an outage clears on
     * its own.
     */
    private function hrisEmployees() {
        $cacheKey = 'hris.employees';
        $cached   = Cache::get($cacheKey);
        if (is_array($cached) && count($cached)) {
            return $cached;
        }

        $decoded = json_decode($this->fetchHrisEmployeesRaw(), true);
        if (!is_array($decoded)) {
            $decoded = [];
        }
        $employees = array_values(array_filter($decoded, function ($emp) {
            return is_array($emp) && !empty($emp['fullnamewithdept']);
        }));

        if (count($employees)) {
            Cache::put($cacheKey, $employees, now()->addMinutes(30));
        }

        return $employees;
    }

    private function fetchHrisEmployeesRaw() {
        $department = Auth::user()->department->name;
        $options = [
            'http' => [
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query(['call_func' => 'get_array_emp', 'department' => $department]),
                'ignore_errors' => true
            ],
            'ssl' => [
                'verify_peer'      => false,
                'verify_peer_name' => false
            ]
        ];

        $context = stream_context_create($options);
        $employees = file_get_contents(config('app.api_path') . "hris-api-2.php", false, $context);

        return $employees === false ? '[]' : $employees;
    }

    public function exportUsersToExcel()
    {
        $users = User::all(); // Fetch data from the database
        return Excel::download(new BladeViewExport($users), 'users.xlsx');
    }

}
