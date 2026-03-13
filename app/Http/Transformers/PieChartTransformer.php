<?php

namespace App\Http\Transformers;


use App\Helpers\Helper;
/**
 * Class PieChartTransformer
 *
 * This handles the standardized formatting of the API response we need to provide for
 * the pie charts
 *
 * @return \Illuminate\Http\Response
 * @since [v6.0.11]
 * @author [A. Gianotto] [<snipe@snipe.net>]
 */
class PieChartTransformer
{
    public function transformPieChartDate($totals)
    {

        $labels = [];
        $counts = [];
        $default_color_count = 0;
        $colors_array = [];

        foreach ($totals as $total) {

            if ($total['count'] > 0) {

                $labels[] = $total['label']." (".$total['count'].")";
                $counts[] = $total['count'];

                if (isset($total['color'])) {
                    $colors_array[] = $total['color'];
                } else {
                    $colors_array[] = Helper::defaultChartColors($default_color_count);
                    $default_color_count++;
                }
            }
        }

        $results = [
            'labels' => $labels,
            'datasets' => [[
                'data' => $counts,
                'backgroundColor' => $colors_array,
                'hoverBackgroundColor' =>  $colors_array,
            ]],
        ];


        return $results;
    }
}
