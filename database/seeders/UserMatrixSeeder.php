<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\PermissionGroup;
use App\Models\Company;
use App\Models\Department;

class UserMatrixSeeder extends Seeder
{
    public function run(): void
    {
        // --- Basisdaten sicherstellen ---
        $this->call([
            SettingsSeeder::class,
            CompanySeeder::class,
            DepartmentSeeder::class,
            GroupMatrixSeeder::class,
        ]);

        // Basis-IDs für Pflichtfelder (falls notwendig)
        $companyId = Company::value('id');
        $deptId    = Department::value('id');

        $pwd = Hash::make(env('MATRIX_PWD', 'password'));
        $gid = fn(string $name) => (int) PermissionGroup::where('name', $name)->value('id');

        // Helper zum Anhängen an Gruppen
        $attach = function (?int $userId, string $groupName) use ($gid) {
            if (!$userId) {
                echo "skip null user for {$groupName}\n";
                return;
            }
            $groupId = $gid($groupName);
            if ($groupId) {
                DB::table('users_groups')->updateOrInsert(
                    ['user_id' => $userId, 'group_id' => $groupId],
                    []
                );
            }
        };

        // --- Superuser (volle Rechte, keine Gruppen nötig) ---
        $super = User::updateOrCreate(
            ['username' => 'superuser_matrix'],
            [
                'first_name'    => 'Super',
                'last_name'     => 'User',
                'email'         => 'superuser.matrix@example.org',
                'password'      => $pwd,
                'activated'     => 1,
                'company_id'    => $companyId,
                'department_id' => $deptId,
            ]
        );
        DB::table('users')->where('id', $super->id)->update([
            'permissions' => json_encode(['superuser' => '1']),
        ]);

        // --- User ohne Zugriffe ---
        $uNone = User::updateOrCreate(
            ['username' => 'user_none'],
            [
                'first_name'    => 'No',
                'last_name'     => 'Access',
                'email'         => 'user.none@example.org',
                'password'      => $pwd,
                'activated'     => 1,
                'company_id'    => $companyId,
                'department_id' => $deptId,
            ]
        );

        // --- User nur mit Assets (View) ---
        $uAssets = User::updateOrCreate(
            ['username' => 'user_assets_view'],
            [
                'first_name'    => 'Assets',
                'last_name'     => 'View',
                'email'         => 'user.assets.view@example.org',
                'password'      => $pwd,
                'activated'     => 1,
                'company_id'    => $companyId,
                'department_id' => $deptId,
            ]
        );
        $attach($uAssets->id, 'grp_view');

        // --- PF-User: View / Edit / Delete ---
        $uPfView = User::updateOrCreate(
            ['username' => 'user_pf_view'],
            [
                'first_name'    => 'PF',
                'last_name'     => 'View',
                'email'         => 'user.pf.view@example.org',
                'password'      => $pwd,
                'activated'     => 1,
                'company_id'    => $companyId,
                'department_id' => $deptId,
            ]
        );
        $attach($uPfView->id, 'grp_pf_view');

        $uPfEdit = User::updateOrCreate(
            ['username' => 'user_pf_edit'],
            [
                'first_name'    => 'PF',
                'last_name'     => 'Edit',
                'email'         => 'user.pf.edit@example.org',
                'password'      => $pwd,
                'activated'     => 1,
                'company_id'    => $companyId,
                'department_id' => $deptId,
            ]
        );
        $attach($uPfEdit->id, 'grp_pf_edit');

        $uPfDel = User::updateOrCreate(
            ['username' => 'user_pf_delete'],
            [
                'first_name'    => 'PF',
                'last_name'     => 'Delete',
                'email'         => 'user.pf.delete@example.org',
                'password'      => $pwd,
                'activated'     => 1,
                'company_id'    => $companyId,
                'department_id' => $deptId,
            ]
        );
        $attach($uPfDel->id, 'grp_pf_delete');

        // --- je Matrix-Gruppe ein eigener User ---
        foreach (PermissionGroup::pluck('id', 'name') as $name => $groupId) {
            $u = User::updateOrCreate(
                ['username' => "user_{$name}"],
                [
                    'first_name'    => 'Grp',
                    'last_name'     => strtoupper($name),
                    'email'         => "user.{$name}@example.org",
                    'password'      => $pwd,
                    'activated'     => 1,
                    'company_id'    => $companyId,
                    'department_id' => $deptId,
                ]
            );
            DB::table('users_groups')->updateOrInsert(
                ['user_id' => $u->id, 'group_id' => $groupId],
                []
            );
        }

        // --- Kombi-User: View + Edit + Delete ---
        $uVed = User::updateOrCreate(
            ['username' => 'user_combo_ved'],
            [
                'first_name'    => 'Combo',
                'last_name'     => 'VED',
                'email'         => 'user.combo.ved@example.org',
                'password'      => $pwd,
                'activated'     => 1,
                'company_id'    => $companyId,
                'department_id' => $deptId,
            ]
        );
        foreach (['grp_view', 'grp_edit', 'grp_delete'] as $g) {
            $attach($uVed->id, $g);
        }

        // --- Multi-None-User in drei leeren Gruppen ---
        $uMultiNone = User::updateOrCreate(
            ['username' => 'user_multi_none'],
            [
                'first_name'    => 'Multi',
                'last_name'     => 'None',
                'email'         => 'user.multi.none@example.org',
                'password'      => $pwd,
                'activated'     => 1,
                'company_id'    => $companyId,
                'department_id' => $deptId,
            ]
        );
        foreach (['grp_none_1', 'grp_none_2', 'grp_none_3'] as $g) {
            $attach($uMultiNone->id, $g);
        }
    }
}
