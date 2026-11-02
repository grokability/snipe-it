<?php

namespace App\Http\Controllers;

use App\Enums\FileStorage;
use App\Helpers\Helper;
use App\Models\Setting;
use Com\Tecnick\Barcode\Barcode;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
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
        // Opt this route out of Laravel's session "previous URL"
        // tracking so it can't erroneously end up as the "previous url"
        // on some servers.
        request()->headers->set('X-Requested-With', 'XMLHttpRequest');

        $settings = Setting::getSettings();

        if ($settings->label2_2d_type === 'none') {
            return false;
        }

        $unavailable = fn () => abort(404, trans('general.generic_model_not_found', ['model' => trans('general.item')]));

        if (! array_key_exists($object_type, self::$map_show_route)) {
            $unavailable();
        }

        $object = parent::getMapObjectType()[$object_type]::withTrashed()->find($id);

        if (! $object || ! Gate::allows('view', $object)) {
            $unavailable();
        }

        $size = Helper::barcodeDimensions($settings->label2_2d_type);
        $qr_key = FileStorage::Barcodes->publicPath().'qr-'.str_slug($object_type).'-'.str_slug($id).'.png';

        if (! Storage::disk('public')->exists($qr_key)) {
            $barcode = new Barcode;
            $barcode_obj = $barcode->getBarcodeObj(
                $settings->label2_2d_type,
                route(self::$map_show_route[$object_type], $id),
                $size['height'],
                $size['width'],
                'black',
                [-2, -2, -2, -2]
            );
            Storage::disk('public')->put($qr_key, $barcode_obj->getPngData());
        }

        // Buffered rather than StreamedResponse or a redirect to the
        // storage URL. StreamedResponse was truncating bodies on S3
        // somewhere in the readStream pipeline, and a 302 to the
        // storage URL leaves the browser with a text/html 302 body on
        // the img fetch, which Safari refuses to render as an image.
        // QR PNGs are a few KB, in-memory is fine.
        return response(Storage::disk('public')->get($qr_key), 200, [
            'Content-type' => 'image/png',
        ]);
    }
}
