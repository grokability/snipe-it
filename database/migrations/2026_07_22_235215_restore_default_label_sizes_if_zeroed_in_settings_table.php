<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('settings')
            ->where(function ($query) {
                $query
                    ->where('labels_per_page', '<=', 0)
                    ->orWhere('labels_width', '<=', 0)
                    ->orWhere('labels_height', '<=', 0)
                    ->orWhere('labels_fontsize', '<=', 0)
                    ->orWhere('labels_pagewidth', '<=', 0)
                    ->orWhere('labels_pageheight', '<=', 0)
                    ->orWhereNull('labels_per_page')
                    ->orWhereNull('labels_width')
                    ->orWhereNull('labels_height')
                    ->orWhereNull('labels_fontsize')
                    ->orWhereNull('labels_pagewidth')
                    ->orWhereNull('labels_pageheight');
            })
            ->update([
                'labels_per_page' => 30,
                'labels_width' => 2.625,
                'labels_height' => 1.0,
                'labels_pmargin_left' => 0.21975,
                'labels_pmargin_right' => 0.21975,
                'labels_pmargin_top' => 0.5,
                'labels_pmargin_bottom' => 0.5,
                'labels_display_bgutter' => 0.07,
                'labels_display_sgutter' => 0.05,
                'labels_fontsize' => 9,
                'labels_pagewidth' => 8.5,
                'labels_pageheight' => 11.0,
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
