<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ModalController extends Controller
{
    /**
     * Load the modal views after confirming they are in the allowed_types array.
     * The allowed types away just prevents shithead skiddies from fuzzing the urls
     * with automated scripts and junking up the logs. - snipe
     *
     * @version    v5.3.7-pre
     *
     * @author [Brady Wetherington] [<uberbrady@gmail.com>]
     * @author [A. Gianotto] [<snipe@snipe.net]
     *
     * @return View
     */
    public function show(Request $request, $type, $itemId = null)
    {

        // These values should correspond to a file in resources/views/modals/
        $allowed_types = [
            'category',
            'company',
            'kit-model',
            'kit-license',
            'kit-consumable',
            'kit-accessory',
            'location',
            'maintenance-type',
            'manufacturer',
            'model',
            'statuslabel',
            'supplier',
            'upload-file',
            'user',
            'add-note',
        ];

        if (in_array($type, $allowed_types)) {
            if (in_array($type, ['kit-model', 'kit-license', 'kit-consumable', 'kit-accessory']) && empty($itemId)) {
                abort(404, 'Kit modal requires a kit id');
            }

            $view = view("blade.modals.{$type}");

            if ($type == 'statuslabel') {
                $view->with('statuslabel_types', Helper::statusTypeList($request->query('scope')));
            }

            if (in_array($type, ['kit-model', 'kit-license', 'kit-consumable', 'kit-accessory'])) {
                $view->with('kitId', $itemId);
            }

            return $view;
        }

        abort(404, 'Page not found');

    }
}
