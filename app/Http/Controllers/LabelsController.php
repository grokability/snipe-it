<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Category;
use App\Models\Company;
use App\Models\CustomField;
use App\Models\Labels\Label;
use App\Models\Location;
use App\Models\Manufacturer;
use App\Models\Setting;
use App\Models\Supplier;
use App\Models\User;
use App\View\Label as LabelView;

class LabelsController extends Controller
{
    /**
     * Returns the Label view with test data
     *
     * @param string $labelName
     * @author Grant Le Roux <grant.leroux+snipe-it@gmail.com>
     */
    public function show(string $labelName)
    {
        $labelName = str_replace('/', '\\', $labelName);
        $template = Label::find($labelName);

        $exampleAsset = new Asset();

        $exampleAsset->id = 999999;
        $exampleAsset->name = 'JEN-867-5309';
        $exampleAsset->asset_tag = '100001';
        $exampleAsset->serial = 'SN9876543210';
        $exampleAsset->asset_eol_date = '2025-01-01';
        $exampleAsset->order_number = '12345';
        $exampleAsset->purchase_date = '2023-01-01';
        $exampleAsset->status_id = 1;
        $exampleAsset->location_id = 1;

        $exampleAsset->company = new Company([
            'name' => trans('admin/labels/table.example_company'),
            'phone' => '1-555-555-5555',
            'email' => 'company@example.com',
        ]);

        $exampleAsset->setRelation('assignedTo', new User(['first_name' => 'Luke', 'last_name' => 'Skywalker']));
        $exampleAsset->defaultLoc = new Location(['name' => trans('admin/labels/table.example_defaultloc'), 'phone' => '1-555-555-5555']);
        $exampleAsset->location = new Location(['name' => trans('admin/labels/table.example_location'), 'phone' => '1-555-555-5555']);

        $exampleAsset->model = new AssetModel();
        $exampleAsset->model->id = 999999;
        $exampleAsset->model->name = trans('admin/labels/table.example_model');
        $exampleAsset->model->model_number = 'MDL5678';
        $exampleAsset->model->manufacturer = new Manufacturer();
        $exampleAsset->model->manufacturer->id = 999999;
        $exampleAsset->model->manufacturer->name = trans('admin/labels/table.example_manufacturer');
        $exampleAsset->model->manufacturer->support_email = 'support@test.com';
        $exampleAsset->model->manufacturer->support_phone = '1-555-555-5555';
        $exampleAsset->model->manufacturer->support_url = 'https://example.com';
        $exampleAsset->supplier = new Supplier(['name' => trans('admin/labels/table.example_company')]);
        $exampleAsset->model->category = new Category();
        $exampleAsset->model->category->id = 999999;
        $exampleAsset->model->category->name = trans('admin/labels/table.example_category');

        $customFieldColumns = CustomField::where('field_encrypted', '=', 0)->pluck('db_column');

        collect(explode(';', Setting::getSettings()->label2_fields))
            ->filter()
            ->each(function ($item) use ($customFieldColumns, $exampleAsset) {
               $pair = explode('=', $item);
               
                if (array_key_exists(1, $pair)) {
                        if ($customFieldColumns->contains($pair[1])) {
                            $exampleAsset->{$pair[1]} = "{{$pair[0]}}";
                        }
                    }
            });

        $settings = Setting::getSettings();
        if (request()->has('settings')) {
            $overrides = request()->get('settings');
            foreach ($overrides as $key => $value) {
                $settings->$key = $value;
            }
        }

        return (new LabelView())
            ->with('assets', collect([$exampleAsset]))
            ->with('settings', $settings)
            ->with('template', $template)
            ->with('bulkedit', false)
            ->with('count', 0);

    }
}
