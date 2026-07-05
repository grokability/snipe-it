<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Models\Setting;
use Com\Tecnick\Barcode\Barcode;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class QrCodeController extends Controller
{
    public static $map_show_route = [
        'accessories' => 'accessories.show',
        'assets' => 'hardware.show',
        'companies' => 'companies.show',
        'components' => 'components.show',
        'consumables' => 'consumables.show',
        'hardware' => 'hardware.show',
        'licenses' => 'licenses.show',
        'locations' => 'locations.show',
        'models' => 'models.show',
        'users' => 'users.show',
    ];

    public function show($object_type, $id): Response|BinaryFileResponse|string|bool
    {
        $settings = Setting::getSettings();

        if ($settings->label2_2d_type === 'none') {
            return false;
        }

        if (! array_key_exists($object_type, self::$map_show_route)) {
            return $object_type.' is not a valid type.';
        }

        $object = self::$map_object_type[$object_type]::withTrashed()->find($id);

        if (! $object) {
            return 'That item is invalid';
        }

        $this->authorize('view', $object);

        $size = Helper::barcodeDimensions($settings->label2_2d_type);
        $qr_file = public_path().'/uploads/barcodes/qr-'.str_slug($object_type).'-'.str_slug($id).'.png';

        if (file_exists($qr_file)) {
            return response()->file($qr_file, ['Content-type' => 'image/png']);
        }

        $barcode = new Barcode;
        $barcode_obj = $barcode->getBarcodeObj(
            $settings->label2_2d_type,
            route(self::$map_show_route[$object_type], $id),
            $size['height'],
            $size['width'],
            'black',
            [-2, -2, -2, -2]
        );
        file_put_contents($qr_file, $barcode_obj->getPngData());

        return response($barcode_obj->getPngData())->header('Content-type', 'image/png');
    }
}
