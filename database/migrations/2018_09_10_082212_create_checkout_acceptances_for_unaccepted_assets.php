<?php

use App\Models\Asset;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateCheckoutAcceptancesForUnacceptedAssets extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Get all assets not accepted
        $assets = DB::table('assets')->where('assigned_type', User::class)->where('accepted', 'pending')->get();

        $acceptances = [];

        foreach ($assets as $asset) {
            $acceptances[] = [
                'checkoutable_type' => Asset::class,
                'checkoutable_id' => $asset->id,
                'assigned_to_id' => $asset->assigned_to,
            ];
        }

        DB::table('checkout_acceptances')->insert($acceptances);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
