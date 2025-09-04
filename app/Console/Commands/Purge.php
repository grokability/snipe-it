<?php

namespace App\Console\Commands;

use App\Models\Accessory;
use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Category;
use App\Models\Component;
use App\Models\Consumable;
use App\Models\License;
use App\Models\Location;
use App\Models\Manufacturer;
use App\Models\Statuslabel;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class Purge extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'snipeit:purge {--force=false}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Purge all soft-deleted deleted records in the database. This will rewrite history for items that have been edited, or checked in or out. It will also rewrite history for users associated with deleted items.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $force = $this->option('force');
        if (($this->confirm("\n****************************************************\nTHIS WILL PURGE ALL SOFT-DELETED ITEMS IN YOUR SYSTEM. \nThere is NO undo. This WILL permanently destroy \nALL of your deleted data. \n****************************************************\n\nDo you wish to continue? No backsies! [y|N]")) || $force == 'true') {

            /**
             * Delete assets
             */
            $assets = Asset::whereNotNull('deleted_at')->withTrashed()->get();
            $assetcount = $assets->count();
            $this->info($assets->count().' assets purged.');
            $asset_assoc = 0;
            $maintenances = 0;

            foreach ($assets as $asset) {
                $this->info('- Asset "'.$asset->display_name.'" deleted.');
                $asset_assoc += $asset->assetlog()->count();
                $asset->assetlog()->forceDelete();
                $maintenances += $asset->maintenances()->count();
                $asset->maintenances()->forceDelete();
                $asset->forceDelete();
            }

            $this->info($asset_assoc.' corresponding log records purged.');
            $this->info($maintenances.' corresponding maintenance records purged.');

            $locations = Location::whereNotNull('deleted_at')->withTrashed()->get();
            $this->info($locations->count().' locations purged.');
            foreach ($locations as $location) {
                $this->info('- Location "'.$location->name.'" deleted.');
                $location->forceDelete();
            }

            $accessories = Accessory::whereNotNull('deleted_at')->withTrashed()->get();
            $accessory_assoc = 0;
            $this->info($accessories->count().' accessories purged.');
            foreach ($accessories as $accessory) {
                $this->info('- Accessory "'.$accessory->name.'" deleted.');
                $accessory_assoc += $accessory->assetlog()->count();
                $accessory->assetlog()->forceDelete();
                $accessory->forceDelete();
            }
            $this->info($accessory_assoc.' corresponding log records purged.');

            $consumables = Consumable::whereNotNull('deleted_at')->withTrashed()->get();
            $this->info($consumables->count().' consumables purged.');
            foreach ($consumables as $consumable) {
                $this->info('- Consumable "'.$consumable->name.'" deleted.');
                $consumable->assetlog()->forceDelete();
                $consumable->forceDelete();
            }

            $components = Component::whereNotNull('deleted_at')->withTrashed()->get();
            $this->info($components->count().' components purged.');
            foreach ($components as $component) {
                $this->info('- Component "'.$component->name.'" deleted.');
                $component->assetlog()->forceDelete();
                $component->forceDelete();
            }

            $licenses = License::whereNotNull('deleted_at')->withTrashed()->get();
            $this->info($licenses->count().' licenses purged.');
            foreach ($licenses as $license) {
                $this->info('- License "'.$license->name.'" deleted.');
                $license->assetlog()->forceDelete();
                $license->licenseseats()->forceDelete();
                $license->forceDelete();
            }

            $models = AssetModel::whereNotNull('deleted_at')->withTrashed()->get();
            $this->info($models->count().' asset models purged.');
            foreach ($models as $model) {
                $this->info('- Asset Model "'.$model->name.'" deleted.');
                $model->forceDelete();
            }

            $categories = Category::whereNotNull('deleted_at')->withTrashed()->get();
            $this->info($categories->count().' categories purged.');
            foreach ($categories as $category) {
                $this->info('- Category "'.$category->name.'" deleted.');
                $category->forceDelete();
            }

            $suppliers = Supplier::whereNotNull('deleted_at')->withTrashed()->get();
            $this->info($suppliers->count().' suppliers purged.');
            foreach ($suppliers as $supplier) {
                $this->info('- Supplier "'.$supplier->name.'" deleted.');
                $supplier->forceDelete();
            }

            $users = User::whereNotNull('deleted_at')->where('show_in_list', '!=', '0')->withTrashed()->get();
            $this->info($users->count().' users purged.');
            $user_assoc = 0;
            foreach ($users as $user) {

                $rel_path = 'private_uploads/users';
                $filenames = Actionlog::where('action_type', 'uploaded')
                    ->where('item_id', $user->id)
                    ->pluck('filename');
                foreach($filenames as $filename) {
                    try {
                        if (Storage::exists($rel_path . '/' . $filename)) {
                            Storage::delete($rel_path . '/' . $filename);
                        }
                    } catch (\Exception $e) {
                        Log::info('An error occurred while deleting files: ' . $e->getMessage());
                    }
                }
                $this->info('- User "'.$user->username.'" deleted.');
                $user_assoc += $user->userlog()->count();
                $user->userlog()->forceDelete();
                $user->forceDelete();
            }
            $this->info($user_assoc.' corresponding user log records purged.');

            $manufacturers = Manufacturer::whereNotNull('deleted_at')->withTrashed()->get();
            $this->info($manufacturers->count().' manufacturers purged.');
            foreach ($manufacturers as $manufacturer) {
                $this->info('- Manufacturer "'.$manufacturer->name.'" deleted.');
                $manufacturer->forceDelete();
            }

            $status_labels = Statuslabel::whereNotNull('deleted_at')->withTrashed()->get();
            $this->info($status_labels->count().' status labels purged.');
            foreach ($status_labels as $status_label) {
                $this->info('- Status Label "'.$status_label->name.'" deleted.');
                $status_label->forceDelete();
            }
        } else {
            $this->info('Action canceled. Nothing was purged.');
        }
    }
}
