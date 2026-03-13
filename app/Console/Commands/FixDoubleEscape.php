<?php

namespace App\Console\Commands;

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
            \App\Models\Asset::class => ['name'],
            \App\Models\License::class => ['name'],
            \App\Models\Consumable::class => ['name'],
            \App\Models\Accessory::class => ['name'],
            \App\Models\Component::class => ['name'],
            \App\Models\Company::class => ['name'],
            \App\Models\Manufacturer::class => ['name'],
            \App\Models\Supplier::class => ['name'],
            \App\Models\Statuslabel::class => ['name'],
            \App\Models\Depreciation::class => ['name'],
            \App\Models\AssetModel::class => ['name'],
            \App\Models\Group::class => ['name'],
            \App\Models\Department::class => ['name'],
            \App\Models\Location::class => ['name'],
            \App\Models\User::class => ['first_name', 'last_name'],
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
