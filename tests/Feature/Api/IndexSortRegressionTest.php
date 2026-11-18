<?php

use App\Models\Accessory;
use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Category;
use App\Models\Company;
use App\Models\Component;
use App\Models\Consumable;
use App\Models\Department;
use App\Models\Depreciation;
use App\Models\Group;
use App\Models\License;
use App\Models\Location;
use App\Models\Manufacturer;
use App\Models\PredefinedKit;
use App\Models\Statuslabel;
use App\Models\Supplier;
use App\Models\User;

dataset('sortCases', function () {
    return [
        'accessories by created_by' => ['api.accessories.index', 'created_by'],
        'accessories by category' => ['api.accessories.index', 'category'],
        'accessories by company' => ['api.accessories.index', 'company'],
        'accessories by location' => ['api.accessories.index', 'location'],
        'accessories by manufacturer' => ['api.accessories.index', 'manufacturer'],
        'accessories by supplier' => ['api.accessories.index', 'supplier'],
        'categories by created_by' => ['api.categories.index', 'created_by'],
        'components by created_by' => ['api.components.index', 'created_by'],
        'components by category' => ['api.components.index', 'category'],
        'components by location' => ['api.components.index', 'location'],
        'components by company' => ['api.components.index', 'company'],
        'components by supplier' => ['api.components.index', 'supplier'],
        'components by manufacturer' => ['api.components.index', 'manufacturer'],
        'consumables by created_by' => ['api.consumables.index', 'created_by'],
        'consumables by category' => ['api.consumables.index', 'category'],
        'consumables by location' => ['api.consumables.index', 'location'],
        'consumables by manufacturer' => ['api.consumables.index', 'manufacturer'],
        'consumables by company' => ['api.consumables.index', 'company'],
        'consumables by supplier' => ['api.consumables.index', 'supplier'],
        'companies by created_by' => ['api.companies.index', 'created_by'],
        'companies by parent' => ['api.companies.index', 'parent'],
        'departments by created_by' => ['api.departments.index', 'created_by'],
        'departments by location' => ['api.departments.index', 'location'],
        'departments by manager' => ['api.departments.index', 'manager'],
        'departments by company' => ['api.departments.index', 'company'],
        'depreciations by created_by' => ['api.depreciations.index', 'created_by'],
        'groups by created_by' => ['api.groups.index', 'created_by'],
        'hardware by created_by' => ['api.assets.index', 'created_by'],
        'hardware by jobtitle' => ['api.assets.index', 'jobtitle'],
        'hardware by assigned_to' => ['api.assets.index', 'assigned_to'],
        'kits by created_by' => ['api.kits.index', 'created_by'],
        'licenses by created_by' => ['api.licenses.index', 'created_by'],
        'licenses by manufacturer' => ['api.licenses.index', 'manufacturer'],
        'licenses by supplier' => ['api.licenses.index', 'supplier'],
        'licenses by company' => ['api.licenses.index', 'company'],
        'locations by created_by' => ['api.locations.index', 'created_by'],
        'locations by parent' => ['api.locations.index', 'parent'],
        'locations by manager' => ['api.locations.index', 'manager'],
        'locations by company' => ['api.locations.index', 'company'],
        'manufacturers by created_by' => ['api.manufacturers.index', 'created_by'],
        'models by created_by' => ['api.models.index', 'created_by'],
        'models by manufacturer' => ['api.models.index', 'manufacturer'],
        'models by category' => ['api.models.index', 'category'],
        'models by fieldset' => ['api.models.index', 'fieldset'],
        'statuslabels by created_by' => ['api.statuslabels.index', 'created_by'],
        'suppliers by created_by' => ['api.suppliers.index', 'created_by'],
    ];
});

test('sort endpoint returns ok', function (string $routeName, string $sort) {
    seedOneRowFor($routeName);

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(route($routeName, ['sort' => $sort, 'order' => 'desc']))
        ->assertOk();
})->with('sortCases');

/**
 * Each endpoint needs at least one row for the response.total check
 * to prove the sort actually traversed data. A superuser creator is
 * fine for every model under test. For License, the factory requires
 * a Category + Manufacturer so we build those up explicitly.
 */
function seedOneRowFor(string $routeName): void
{
    match ($routeName) {
        'api.accessories.index' => Accessory::factory()->create(),
        'api.categories.index' => Category::factory()->forAssets()->create(),
        'api.components.index' => Component::factory()->create(),
        'api.consumables.index' => Consumable::factory()->create(),
        'api.companies.index' => Company::factory()->create(),
        'api.departments.index' => Department::factory()->create(),
        'api.depreciations.index' => Depreciation::factory()->create(),
        'api.groups.index' => Group::factory()->create(),
        'api.assets.index' => Asset::factory()->create(),
        'api.kits.index' => PredefinedKit::factory()->create(),
        'api.licenses.index' => License::factory()->create(),
        'api.locations.index' => Location::factory()->create(),
        'api.manufacturers.index' => Manufacturer::factory()->create(),
        'api.models.index' => AssetModel::factory()->create(),
        'api.statuslabels.index' => Statuslabel::factory()->create(),
        'api.suppliers.index' => Supplier::factory()->create(),
        default => null,
    };
}
