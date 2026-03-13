<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;

/**
 * This controller provide the health route  for
 * the Snipe-IT Asset Management application.
 *
 * @version   v1.0
 *
 * @return \Illuminate\Http\JsonResponse

 */
class HealthController extends BaseController
{

    public function __construct()
    {
        $this->middleware('health');
    }


    /**
     * Returns a fixed JSON content ({ "status": "ok"}) which indicate the app is up and running
     */
    public function get()
    {

        try {

            if (DB::select('select 2 + 2')) {
                $db_status = 'ok';
            } else {
                $db_status = 'Could not connect to database';
            }

        } catch (\Exception $e) {
            $db_status = 'Could not connect to database';

        }


        if (is_writable(storage_path('logs'))) {
            $filesystem_status = 'ok';
        } else {
            $filesystem_status = 'Could not write to storage/logs';
        }

        if (($filesystem_status!='ok') || ($db_status!='ok')) {
            return response()->json([
                'status' =>
                    [
                        'database' => $db_status,
                        'filesystem' => $filesystem_status,
                    ]
            ], 500);
        }


        return response()->json([
            'status' => 'ok',
        ]);

    }
}
