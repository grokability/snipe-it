<?php
/*! \mainpage Snipe-IT Code Documentation
 *
 * \section intro_sec Introduction
 *
 * This documentation is designed to allow developers to easily understand
 * the backend code of Snipe-IT. Familiarity with the PHP language is assumed,
 * and experience with the Laravel framework (version 5.2) will be very helpful.
 *
 * **THIS DOCUMENTATION DOES NOT COVER INSTALLATION.** If you're here and you're not a
 * developer, you're probably in the wrong place. Please see the
 * [Installation documentation](https://snipe-it.readme.io) for
 * information on how to install Snipe-IT.
 *
 * To learn how to set up a development environment and get started developing for Snipe-IT,
 * please see the [contributing documentation](https://snipe-it.readme.io/docs/contributing-overview).
 *
 * Only the Snipe-IT specific controllers, models, helpers, service providers,
 * etc have been included in this documentation (excluding vendors, Laravel core, etc)
 * for simplicity.
 */

namespace App\Http\Controllers;

use App\Models\Accessory;
use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Component;
use App\Models\Consumable;
use App\Models\License;
use App\Models\Location;
use App\Models\Maintenance;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

abstract class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    static $map_object_type = [
        'accessories' => Accessory::class,
        'maintenances' => Maintenance::class,
        'assets' => Asset::class,
        'audits' => Asset::class,
        'components' => Component::class,
        'consumables' => Consumable::class,
        'hardware' => Asset::class,
        'licenses' => License::class,
        'locations' => Location::class,
        'models' => AssetModel::class,
        'users' => User::class,
    ];

    static $map_storage_path = [
        'accessories' => 'private_uploads/accessories/',
        'maintenances' => 'private_uploads/maintenances/',
        'assets' => 'private_uploads/assets/',
        'audits' => 'private_uploads/audits/',
        'components' => 'private_uploads/components/',
        'consumables' => 'private_uploads/consumables/',
        'hardware' => 'private_uploads/assets/',
        'licenses' => 'private_uploads/licenses/',
        'locations' => 'private_uploads/locations/',
        'models' => 'private_uploads/models/',
        'users' => 'private_uploads/users/',
    ];

    static $map_file_prefix= [
        'accessories' => 'accessory',
        'maintenances' => 'maintenance',
        'assets' => 'asset',
        'audits' => 'audits',
        'components' => 'component',
        'consumables' => 'consumable',
        'hardware' => 'asset',
        'licenses' => 'license',
        'locations' => 'location',
        'models' => 'model',
        'users' => 'user',
    ];

    public function __construct()
    {
        view()->share('signedIn', Auth::check());
        view()->share('user', auth()->user());
    }
}
