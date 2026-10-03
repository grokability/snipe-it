<?php

namespace App\Models\Labels;

use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Category;
use App\Models\Company;
use App\Models\Location;
use App\Models\Manufacturer;
use App\Models\Supplier;
use App\Models\User;
use Carbon\Carbon;

class LabelPreviewAsset
{
    public static function make(): Asset
    {
        $asset = new Asset();

        $asset->id = 999999;
        $asset->name = 'JEN-867-5309';
        $asset->asset_tag = '100001';
        $asset->serial = 'SN9876543210';
        $asset->asset_eol_date = Carbon::parse('2025-01-01');
        $asset->warranty_months = 24;
        $asset->order_number = '12345';
        $asset->purchase_date = Carbon::parse('2023-01-01');
        $asset->status_id = 1;
        $asset->location_id = 1;

        $asset->setRelation('company', new Company([
            'name' => trans('admin/labels/table.example_company'),
            'phone' => '1-555-555-5555',
            'email' => 'company@example.com',
            'logo' => 'label-preview-logo.png',
        ]));

        $asset->setRelation('assignedTo', new User([
            'first_name' => 'Luke',
            'last_name' => 'Skywalker',
        ]));

        $asset->setRelation('defaultLoc', new Location([
            'name' => trans('admin/labels/table.example_defaultloc'),
            'phone' => '1-555-555-5555',
        ]));

        $asset->setRelation('location', new Location([
            'name' => trans('admin/labels/table.example_location'),
            'phone' => '1-555-555-5555',
        ]));

        $model = new AssetModel();
        $model->id = 999999;
        $model->name = trans('admin/labels/table.example_model');
        $model->model_number = 'MDL5678';

        $manufacturer = new Manufacturer();
        $manufacturer->id = 999999;
        $manufacturer->name = trans('admin/labels/table.example_manufacturer');
        $manufacturer->support_email = 'support@test.com';
        $manufacturer->support_phone = '1-555-555-5555';
        $manufacturer->support_url = 'https://example.com';

        $model->setRelation('manufacturer', $manufacturer);

        $category = new Category();
        $category->id = 999999;
        $category->name = trans('admin/labels/table.example_category');

        $model->setRelation('category', $category);

        $asset->setRelation('model', $model);

        $asset->setRelation('supplier', new Supplier([
            'name' => trans('admin/labels/table.example_company'),
        ]));

        $asset->is_label_preview = true;

        return $asset;
    }
}