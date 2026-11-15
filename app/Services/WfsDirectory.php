<?php

namespace App\Services;

use App\Models\Department;

/**
 * Read-only access to WFS users, for Admin > Departments: the approver pickers,
 * and "Import current approvers from WFS", which fills a department's empty
 * approver fields the way api/wfs-api.php used to look them up (by department
 * name in WFS users). Uses the same connection as the api/ scripts.
 */
class WfsDirectory
{
    private static $conn;
    private static $users;

    /**
     * @return resource|false
     */
    public static function connection()
    {
        if (self::$conn === null) {
            $conn = false;
            // config.php sets $conn (and other locals) in this function's scope.
            include base_path('api/config.php');
            self::$conn = $conn ?: false;
        }

        return self::$conn;
    }

    public static function available()
    {
        return (bool) self::connection();
    }

    /**
     * Active WFS users keyed by id: [id => ['id','name','designation','department','division','is_alternate']].
     *
     * @return array
     */
    public static function users()
    {
        if (self::$users !== null) {
            return self::$users;
        }

        self::$users = [];
        $conn = self::connection();
        if (!$conn) {
            return self::$users;
        }

        $query = sqlsrv_query($conn, "select id, name, designation, department, division, is_alternate
            from users where isActive = 1 order by name");
        if (!$query) {
            return self::$users;
        }
        while ($row = sqlsrv_fetch_array($query, SQLSRV_FETCH_ASSOC)) {
            self::$users[(int) $row['id']] = [
                'id'           => (int) $row['id'],
                'name'         => trim((string) $row['name']),
                'designation'  => trim((string) $row['designation']),
                'department'   => trim((string) $row['department']),
                'division'     => trim((string) $row['division']),
                'is_alternate' => (int) $row['is_alternate'],
            ];
        }

        return self::$users;
    }

    /**
     * "Name — Designation" for a WFS user id, or null if not an active WFS user.
     */
    public static function label($id)
    {
        $users = self::users();
        if (!$id || !isset($users[(int) $id])) {
            return null;
        }
        $user = $users[(int) $id];

        return $user['name'] . ($user['designation'] !== '' ? ' — ' . $user['designation'] : '');
    }

    /**
     * The approvers WFS would have picked for this department under the old
     * name-based lookup (api/wfs-api.php before approvers moved here):
     *   1st = WFS user with designation MANAGER whose department list has this
     *         department (exact entry first, then substring), is_alternate 0/1;
     *   2nd = Division Manager of that department's division (MRS).
     * Keys match Department::WFS_APPROVER_FIELDS; a value is null when none found.
     *
     * @return array
     */
    public static function suggestFor(Department $department)
    {
        $found = array_fill_keys(array_keys(Department::WFS_APPROVER_FIELDS), null);
        $conn = self::connection();
        if (!$conn) {
            return $found;
        }

        $manager = self::userByDepartment($department->name, "designation='MANAGER' and is_alternate=0 and isActive=1");
        $alternate = self::userByDepartment($department->name, "designation='MANAGER' and is_alternate=1 and isActive=1");
        $found['wfs_approver_id'] = $manager ? (int) $manager['id'] : null;
        $found['wfs_alternate_approver_id'] = $alternate ? (int) $alternate['id'] : null;

        $anyone = self::userByDepartment($department->name, "1=1");
        $division = $anyone ? trim((string) $anyone['division']) : '';
        if ($division !== '') {
            $like = '%' . $division . '%';
            $divManager = self::first("select TOP 1 id from users where designation like '%Division Manager%' and division like ? and isActive=1 order by id ASC", [$like]);
            $divAlternate = self::first("select TOP 1 id from users where designation like '%Division Manager%' and division like ? and is_alternate=1 and isActive=1 order by id ASC", [$like]);
            $found['wfs_approver2_id'] = $divManager ? (int) $divManager['id'] : null;
            $found['wfs_alternate_approver2_id'] = $divAlternate ? (int) $divAlternate['id'] : null;
        }

        // The old lookup could return the same person as main and alternate.
        if ($found['wfs_alternate_approver_id'] === $found['wfs_approver_id']) {
            $found['wfs_alternate_approver_id'] = null;
        }
        if ($found['wfs_alternate_approver2_id'] === $found['wfs_approver2_id']) {
            $found['wfs_alternate_approver2_id'] = null;
        }

        return $found;
    }

    /**
     * WFS users.department is a '|'-separated list of names: exact list entry
     * first, substring match as the fallback (same as the old wfs-api.php).
     */
    private static function userByDepartment($departmentName, $where)
    {
        $dept = str_replace(['[', '%', '_'], ['[[]', '[%]', '[_]'], trim((string) $departmentName));
        if ($dept === '') {
            return null;
        }
        $matches = [
            "'|' + replace(replace(cast(department as nvarchar(max)), '| ', '|'), ' |', '|') + '|' like ?" => '%|' . $dept . '|%',
            "department like ?" => '%' . $dept . '%',
        ];
        foreach ($matches as $condition => $param) {
            $row = self::first("select TOP 1 * from users where $condition and $where order by id ASC", [$param]);
            if ($row) {
                return $row;
            }
        }

        return null;
    }

    private static function first($sql, array $params)
    {
        $query = sqlsrv_query(self::connection(), $sql, $params);
        if (!$query) {
            return null;
        }
        $row = sqlsrv_fetch_array($query, SQLSRV_FETCH_ASSOC);

        return $row ?: null;
    }
}
