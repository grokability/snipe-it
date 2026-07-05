<?php

namespace App\Http\Transformers;

use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Class SelectlistTransformer
 *
 * This handles the standardized formatting of the API response we need to provide for
 * the rich (text and images) Select2 javascript.
 *
 * @author [A. Gianotto] [<snipe@snipe.net>]
 *
 * @since [v4.0.16]
 *
 * @return Response
 */
class SelectlistTransformer
{
    public function transformSelectlist(LengthAwarePaginator $select_items)
    {
        $items_array = [];

        // Loop through the paginated collection to set the array values
        foreach ($select_items as $select_item) {
            $row = [
                'id' => (int) $select_item->id,
                'text' => ($select_item->use_text) ? $select_item->use_text : $select_item->name,
                'image' => ($select_item->use_image) ? $select_item->use_image : null,
                'tag_color' => ($select_item->tag_color) ? $select_item->tag_color : null,
            ];

            // Optional: when set, select2 renders the option as un-selectable.
            // Used to enforce hierarchy / membership constraints up-front.
            if (! empty($select_item->use_disabled)) {
                $row['disabled'] = true;
            }

            $items_array[] = $row;
        }

        $results = [
            'results' => $items_array,
            'pagination' => [
                'more' => ($select_items->currentPage() >= $select_items->lastPage()) ? false : true,
                'per_page' => $select_items->perPage(),
            ],
            'total_count' => $select_items->total(),
            'page' => $select_items->currentPage(),
            'page_count' => $select_items->lastPage(),
        ];

        return $results;
    }
}
