<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Asset;
use App\Models\AssetModel;

class AddEolDateOnAssetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('assets', function (Blueprint $table) {
            
            if (!Schema::hasColumn('assets', 'asset_eol_date')) {
                $table->date('asset_eol_date')->after('purchase_date')->nullable()->default(null);
            }

            // This is a temporary shim so we don't have to modify the asset observer for migrations where
            // there is a large version difference. (See the AssetObserver notes). This column gets created
            // later in 2023_07_13_052204_denormalized_eol_and_add_column_for_explicit_date_to_assets.php
            // but we have to temporarily create it now so the save method below doesn't break
            if (!Schema::hasColumn('assets', 'eol_explicit')) {
                $table->boolean('eol_explicit')->default(false)->after('asset_eol_date');
            }
        });

        // Chunk the model query to get the models that do have an EOL date
        // We use saveQuietly() here to skip the AssetObserver, since it modifies fields
        // that do not yet exist on the assets table.
        AssetModel::whereNotNull('eol')->chunk(10, function ($models) {
            foreach ($models as $model) {
                foreach ($model->assets as $asset) {

                    if ($asset->purchase_date!='') {
                        $asset->asset_eol_date = $asset->present()->eol_date();
                        $asset->saveQuietly();
                    }

                }
            }
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('assets', function (Blueprint $table) {
            if (Schema::hasColumn('assets', 'asset_eol_date')) {
                $table->dropColumn('asset_eol_date');
            }
        });
    }
}
