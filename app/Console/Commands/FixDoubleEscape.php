<?php

namespace App\Console\Commands;

use App\Models\Accessory;
use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Company;
use App\Models\Component;
use App\Models\Consumable;
use App\Models\Department;
use App\Models\Depreciation;
use App\Models\Group;
use App\Models\License;
use App\Models\Location;
use App\Models\Manufacturer;
use App\Models\Statuslabel;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Console\Command;

class FixDoubleEscape extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'snipeit:unescape';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This should be run to fix some double-escaping issues from earlier versions of Snipe-IT.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $tables = [
            Asset::class => ['name'],
            License::class => ['name'],
            Consumable::class => ['name'],
            Accessory::class => ['name'],
            Component::class => ['name'],
            Company::class => ['name'],
            Manufacturer::class => ['name'],
            Supplier::class => ['name'],
            Statuslabel::class => ['name'],
            Depreciation::class => ['name'],
            AssetModel::class => ['name'],
            Group::class => ['name'],
            Department::class => ['name'],
            Location::class => ['name'],
            User::class => ['first_name', 'last_name'],
        ];

        $count = [];

        foreach ($tables as $classname => $fields) {
            $count[$classname] = [];
            $count[$classname]['classname'] = 0;

            foreach ($fields as $field) {
                $count[$classname]['classname']++;
                $count[$classname][$field] = 0;

                foreach ($classname::where("$field", 'LIKE', '%&%')->get() as $row) {
                    $this->info('Updating '.$field.' for '.$classname);
                    $row->{$field} = html_entity_decode($row->{$field}, ENT_QUOTES);
                    $row->save();
                    $count[$classname][$field]++;
                }
            }
        }

        $this->info('Update complete');
    }
}
