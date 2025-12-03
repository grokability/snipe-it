<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $setting = DB::table('settings')->select(['skin', 'header_color'])->first();

        Schema::table('settings', function (Blueprint $table) {
            $table->string('link_dark_color')->after('header_color')->nullable()->default(null);
            $table->string('link_light_color')->after('header_color')->nullable()->default(null);
            $table->string('nav_link_color')->after('header_color')->nullable()->default(null);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('link_dark_color')->after('skin')->nullable()->default(null);
            $table->string('link_light_color')->after('skin')->nullable()->default(null);
            $table->string('nav_link_color')->after('skin')->nullable()->default(null);
        });


        // Set Snipe-IT defaults
        $link_dark_color = '#89c9ed';
        $link_light_color = '#296282';
        $nav_color = '#ffffff';
        $header_color = '#3c8dbc';

        if ($setting) {

            switch ($setting->skin) {
                case ('green' || 'green-dark'):
                    $header_color = '#00a65a';
                    $link_dark_color = '#00a65a';
                    $link_light_color = '#00a65a';
                    $nav_color = '#ffffff';

                case ('red' || 'red-dark'):
                    $header_color = '#dd4b39';
                    $link_dark_color = '#dd4b39';
                    $link_light_color = '#dd4b39';
                    $nav_color = '#ffffff';

                case ('orange' || 'orange-dark'):
                    $header_color = '#FF851B';
                    $link_dark_color = '#FF851B';
                    $link_light_color = '#FF851B';
                    $nav_color = '#ffffff';

                case ('black' || 'black-dark'):
                    $header_color = '#000000';
                    $link_dark_color = '#111';
                    $link_light_color = '#111';
                    $nav_color = '#ffffff';

                case ('purple' || 'purple-dark'):
                    $header_color = '#605ca8';
                    $link_dark_color = '#605ca8';
                    $link_light_color = '#605ca8';
                    $nav_color = '#ffffff';

                case ('yellow' || 'yellow-dark') :
                    $header_color = '#f39c12';
                    $link_dark_color = '#f39c12';
                    $link_light_color = '#f39c12';
                    $nav_color = '#ffffff';

                case 'contrast':
                    $header_color = '#001F3F';
                    $link_dark_color = '#86cbf2';
                    $link_light_color = '#084d73';
                    $nav_color = '#ffffff';
                    break;
            }

            // Override the header color if the settings have one
            if ($setting->header_color) {
                $header_color = $setting->header_color;
                \Log::debug('A header color was found, so lets use that instead: '.$setting->header_color);
            }


            DB::table('settings')->update([
                'link_light_color' => $link_light_color,
                'link_dark_color' => $link_dark_color,
                'nav_link_color' => $nav_color,
                'header_color' => $header_color]);

            DB::table('users')->whereNull('skin')->update([
                'link_light_color' => $link_light_color,
                'link_dark_color' => $link_dark_color,
                'nav_link_color' => $nav_color]);
        }

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function ($table) {
            $table->dropColumn('link_dark_color');
            $table->dropColumn('link_light_color');
            $table->dropColumn('nav_link_color');
        });

        Schema::table('users', function ($table) {
            $table->dropColumn('link_dark_color');
            $table->dropColumn('link_light_color');
            $table->dropColumn('nav_link_color');
        });
    }
};
