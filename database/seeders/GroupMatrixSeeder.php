<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\PermissionGroup;
use App\Support\PermKeys as K;

class GroupMatrixSeeder extends Seeder
{
    private array $K = [

        // Assets
        'VIEW'   => 'assets.view',
        'CREATE' => 'assets.create',  
        'EDIT'   => 'assets.edit',     
        'DELETE' => 'assets.delete',   

        // Predefined Filters
        'PF_VIEW'   => 'predefinedFilters.view',   
        'PF_EDIT'   => 'predefinedFilters.edit',   
        'PF_DELETE' => 'predefinedFilters.delete', 

    ];

    public function run(): void
    {
        $defs = [
            'grp_none_1' => [],
            'grp_none_2' => [],
            'grp_none_3' => [],

            // Assets
            'grp_view'   => ['assets.view'],
            'grp_edit'   => ['assets.edit'],
            'grp_delete' => ['assets.delete'],

            'grp_view_edit'        => ['assets.view','assets.edit'],
            'grp_view_edit_delete' => ['assets.view','assets.edit','assets.delete'],
            'grp_edit_delete'      => ['assets.edit','assets.delete'],
            'grp_create_delete_no_view' => ['assets.create','assets.delete'],
            'grp_edit_delete_no_view'   => ['assets.edit','assets.delete'],
            'grp_create_edit_no_view'   => ['assets.create','assets.edit'],
            'grp_create'                => ['assets.create'],
            'grp_view_delete'           => ['assets.view','assets.delete'],
        ];

        foreach ($defs as $name => $keys) {
            $g = PermissionGroup::firstOrCreate(['name'=>$name], ['notes'=>'test-matrix']);
            $g->permissions = json_encode(
                collect($keys)->mapWithKeys(fn($k)=>[$k=>'1'])->all(),
                JSON_UNESCAPED_SLASHES
            );
            $g->save();
        }
        
        foreach ([
            'grp_pf_view'   => ['predefinedFilter.view'],
            'grp_pf_edit'   => ['predefinedFilter.edit'],
            'grp_pf_delete' => ['predefinedFilter.delete'],
        ] as $name => $keys) {
            $g = PermissionGroup::firstOrCreate(['name'=>$name], ['notes'=>'test-matrix']);
            $g->permissions = json_encode(
                collect($keys)->mapWithKeys(fn($k)=>[$k=>'1'])->all(),
                JSON_UNESCAPED_SLASHES
            );
            $g->save();
        }
    }
}
