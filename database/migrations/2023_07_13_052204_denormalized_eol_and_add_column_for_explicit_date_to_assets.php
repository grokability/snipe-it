<?php

use App\Models\Asset;
use Carbon\CarbonImmutable;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class DenormalizedEolAndAddColumnForExplicitDateToAssets extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('assets', function (Blueprint $table) {
            if (!Schema::hasColumn('assets', 'eol_explicit')) {
                $table->boolean('eol_explicit')->default(false)->after('asset_eol_date');
            }
        });


        // Update the eol_explicit column with the value from asset_eol_date if it exists and is different from the calculated value
        Asset::whereNotNull('asset_eol_date')->with('model')->chunkById(500, function ($assetsWithEolDates) {
            foreach ($assetsWithEolDates as $asset) {
                if ($asset->asset_eol_date && $asset->purchase_date) {
                    try {
                        $months = CarbonImmutable::parse($asset->asset_eol_date)->diffInMonths($asset->purchase_date);
                    } catch (\Exception $e) {
                        Log::info('asset_eol_date invalid for asset ' . $asset->id);
                    }
                    if ($asset->model->eol) {
                        if ($months != $asset->model->eol) {
                            DB::table('assets')->where('id', $asset->id)->update(['eol_explicit' => true]);
                        }
                    }
                    // if there is NO model eol, but there is a purchase date and an asset_eol_date (which is what is left over) the asset_eol_date has still been explicitly set
                    else {
                        DB::table('assets')->where('id', $asset->id)->update(['eol_explicit' => true]);
                    }
                }
            }
        });

        DB::table('assets')
            ->whereNull('asset_eol_date')
            ->whereNotNull('purchase_date')
            ->whereNotNull('model_id')
            ->join('models', 'assets.model_id', '=', 'models.id')
            ->update([
                'asset_eol_date' => $this->eolUpdateExpression(),
            ]);
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn('eol_explicit');
        });
    }

    /**
     * This method returns the correct database expression for either
     * mysql, postgres, or sqlite depending on the driver being used.
     */
    private function eolUpdateExpression(): Expression
    {
        if (DB::getDriverName() === 'sqlite') {
            return DB::raw("DATE(purchase_date, '+' || (SELECT eol FROM " . DB::getTablePrefix() . "models WHERE models.id = assets.model_id) || ' months')");
        }

        if (DB::getDriverName() === 'pgsql') {
            return DB::raw("date(purchase_date + interval '1 month' * (SELECT eol FROM " . DB::getTablePrefix() . "models WHERE models.id = assets.model_id))");
        }

        // Default to MySQL's method
        return DB::raw('DATE_ADD(purchase_date, INTERVAL ' . DB::getTablePrefix() . 'models.eol MONTH)');
    }
}
