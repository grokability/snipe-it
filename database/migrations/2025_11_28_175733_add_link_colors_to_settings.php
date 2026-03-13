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
            $table->string('nav_link_color')->after('header_color')->nullable()->default('#ffffff');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('link_dark_color')->after('skin')->nullable()->default(null);
            $table->string('link_light_color')->after('skin')->nullable()->default(null);
            $table->string('nav_link_color')->after('skin')->nullable()->default('#ffffff');
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
                    $link_dark_color = '#9ACD32';
                    $link_light_color = '#00a65a';
                    $nav_color = '#ffffff';
                    break;

                case ('red' || 'red-dark'):
                    $header_color = '#dd4b39';
                    $link_dark_color = '#ed9a9a';
                    $link_light_color = '#dd4b39';
                    $nav_color = '#ffffff';
                    break;

                case ('orange' || 'orange-dark'):
                    $header_color = '#FF851B';
                    $link_dark_color = '#FFA500';
                    $link_light_color = '#FF8C00';
                    $nav_color = '#ffffff';
                    break;

                case ('black' || 'black-dark'):
                    $header_color = '#000000';
                    $link_dark_color = '#d4d2d2';
                    $link_light_color = '#454759';
                    $nav_color = '#ffffff';
                    break;

                case ('purple' || 'purple-dark'):
                    $header_color = '#605ca8';
                    $link_dark_color = '#AC83FF';
                    $link_light_color = '#605ca8';
                    $nav_color = '#ffffff';
                    break;

                case ('yellow' || 'yellow-dark') :
                    $header_color = '#FBCC34';
                    $link_dark_color = '#F0E68C';
                    $link_light_color = '#a69f08';
                    $nav_color = '#ffffff';
                    break;

                case 'contrast':
                    $header_color = '#001F3F';
                    $link_dark_color = '#a6c9ed';
                    $link_light_color = '#2d4863';
                    $nav_color = '#ffffff';
                    break;
            }

            // Override the header color if the settings have one
            if ($setting->header_color) {
                $header_color = $setting->header_color;
            }


            DB::table('settings')->update([
                'link_light_color' => $link_light_color,
                'link_dark_color' => $link_dark_color,
                'nav_link_color' => $nav_color,
                'header_color' => $header_color]);
            
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
