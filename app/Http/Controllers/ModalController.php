<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;

class ModalController extends Controller
{

    /** 
     * Load the modal views after confirming they are in the allowed_types array.
     * The allowed types away just prevents shithead skiddies from fuzzing the urls 
     * with automated scripts and junking up the logs. - snipe
     * 
     * @version    v5.3.7-pre
     * @author [Brady Wetherington] [<uberbrady@gmail.com>]
     * @author [A. Gianotto] [<snipe@snipe.net]
     * @return \Illuminate\Contracts\View\View
     */
    public function show ($type, $itemId = null) {

        // These values should correspond to a file in resources/views/modals/
        $allowed_types = [
            'category',
            'kit-model', 
            'kit-license', 
            'kit-consumable', 
            'kit-accessory',
            'location',
            'manufacturer',
            'model',
            'statuslabel',
            'supplier',
            'upload-file',
            'user',
            'add-note',
        ];


        if (in_array($type, $allowed_types)) {
        $view = view("modals.${type}");

            if ($type == "statuslabel") {
                $view->with('statuslabel_types', Helper::statusTypeList());
            }

            if (in_array($type, ['kit-model', 'kit-license', 'kit-consumable', 'kit-accessory'])) {
                $view->with('kitId', $itemId);
            }
            return $view;
        }

        abort(404,'Page not found');
        
    }
}
