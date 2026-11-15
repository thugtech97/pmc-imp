<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Services\WfsDirectory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Admin > Departments: manage departments and their WFS approvers.
 * api/wfs-api.php takes the approvers from here (Department::wfsApprovers()).
 */
class DepartmentController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            abort_unless(auth()->check() && auth()->user()->has_role('Admin'), 403);
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $filter = $request->input('filter', '');

        $departments = Department::withCount('users')
            ->when($search !== '', function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%');
            })
            ->when($filter === 'missing', function ($query) {
                // Departments people submit from but with no 1st approver set.
                $query->whereNull('wfs_approver_id')->has('users');
            })
            ->when($filter === 'in_use', function ($query) {
                $query->has('users');
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $missingCount = Department::whereNull('wfs_approver_id')->has('users')->count();
        $wfsAvailable = WfsDirectory::available();

        return view('admin.departments.index', compact('departments', 'search', 'filter', 'missingCount', 'wfsAvailable'));
    }

    public function create()
    {
        return $this->form(new Department);
    }

    public function store(Request $request)
    {
        $department = Department::create($this->validated($request));

        return redirect()->route('departments.index')->with('success', 'Department "' . $department->name . '" has been created.');
    }

    public function edit(Department $department)
    {
        return $this->form($department);
    }

    public function update(Request $request, Department $department)
    {
        $department->update($this->validated($request, $department));

        return redirect()->route('departments.index')->with('success', 'Department "' . $department->name . '" has been updated.');
    }

    public function destroy(Department $department)
    {
        $users = $department->users()->count();
        if ($users > 0) {
            return back()->with('error', 'Cannot delete "' . $department->name . '": ' . $users . ' user(s) still belong to it. Move them to another department first.');
        }

        $name = $department->name;
        $department->delete();

        return redirect()->route('departments.index')->with('success', 'Department "' . $name . '" has been deleted.');
    }

    /**
     * Fill empty approver fields from WFS users, the way wfs-api.php used to look
     * them up by department name. Never overwrites an approver already set.
     */
    public function importFromWfs()
    {
        if (!WfsDirectory::available()) {
            return back()->with('error', 'Could not connect to WFS, so nothing was imported.');
        }

        $filled = 0;
        $departmentsTouched = 0;
        foreach (Department::orderBy('id')->get() as $department) {
            $empty = array_filter(array_keys(Department::WFS_APPROVER_FIELDS), function ($field) use ($department) {
                return empty($department->{$field});
            });
            if (!$empty) {
                continue;
            }

            $suggested = WfsDirectory::suggestFor($department);
            $changes = [];
            foreach ($empty as $field) {
                if (!empty($suggested[$field])) {
                    $changes[$field] = $suggested[$field];
                }
            }
            if ($changes) {
                $department->update($changes);
                $filled += count($changes);
                $departmentsTouched++;
            }
        }

        return back()->with('success', $filled
            ? "Imported {$filled} approver(s) from WFS into {$departmentsTouched} department(s). Approvers already set were left as they were."
            : 'Nothing to import: WFS had no matching approvers for the empty fields.');
    }

    private function form(Department $department)
    {
        $wfsUsers = WfsDirectory::users();
        $wfsAvailable = WfsDirectory::available();
        $fields = Department::WFS_APPROVER_FIELDS;

        return view('admin.departments.form', compact('department', 'wfsUsers', 'wfsAvailable', 'fields'));
    }

    private function validated(Request $request, Department $department = null)
    {
        $wfsIds = array_keys(WfsDirectory::users());
        $approverRule = ['nullable', 'integer'];
        if ($wfsIds) {
            $approverRule[] = Rule::in($wfsIds);   // must be an active WFS user
        }

        $data = $request->validate([
            'name'                       => ['required', 'string', 'max:150'],
            'wfs_approver_id'            => $approverRule,
            'wfs_alternate_approver_id'  => array_merge($approverRule, ['different:wfs_approver_id']),
            'wfs_approver2_id'           => $approverRule,
            'wfs_alternate_approver2_id' => array_merge($approverRule, ['different:wfs_approver2_id']),
        ], [
            'wfs_alternate_approver_id.different'  => 'The 1st alternate approver must be a different person from the 1st approver.',
            'wfs_alternate_approver2_id.different' => 'The 2nd alternate approver must be a different person from the 2nd approver.',
            '*.in'                                 => 'Pick an active WFS user.',
        ], [
            'wfs_approver_id'            => '1st approver',
            'wfs_alternate_approver_id'  => '1st alternate approver',
            'wfs_approver2_id'           => '2nd approver',
            'wfs_alternate_approver2_id' => '2nd alternate approver',
        ]);

        $data['name'] = preg_replace('/\s+/', ' ', trim($data['name']));

        // The table already has the same department typed many ways; don't add another.
        $key = Department::normalizeName($data['name']);
        $twin = Department::all(['id', 'name'])->first(function ($other) use ($key, $department) {
            return Department::normalizeName($other->name) === $key && (!$department || $other->id !== $department->id);
        });
        if ($twin) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'name' => 'A department named "' . $twin->name . '" already exists.',
            ]);
        }

        // An alternate with no main approver would never be used on its own.
        foreach (['wfs_alternate_approver_id' => 'wfs_approver_id', 'wfs_alternate_approver2_id' => 'wfs_approver2_id'] as $alternate => $main) {
            if (!empty($data[$alternate]) && empty($data[$main])) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    $main => 'Set the main approver before an alternate.',
                ]);
            }
        }

        foreach (array_keys(Department::WFS_APPROVER_FIELDS) as $field) {
            $data[$field] = !empty($data[$field]) ? (int) $data[$field] : null;
        }

        return $data;
    }
}
