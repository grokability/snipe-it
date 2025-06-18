<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixUpAssignedTypeWithoutAssignedTo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'snipeit:assigned-type-fixup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fixes up assets that have an assigned_type but no assigned_to';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        DB::table('assets')->whereNotNull('assigned_type')->whereNull('assigned_to')->update(['assigned_type' => null]);
        $this->info("Assets with an assigned_type but no assigned_to are fixed");
    }
}
