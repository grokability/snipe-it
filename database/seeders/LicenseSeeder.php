<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\License;
use App\Models\LicenseSeat;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class LicenseSeeder extends Seeder
{
    public function run()
    {
        License::truncate();
        LicenseSeat::truncate();

        if (! Category::count()) {
            $this->call(CategorySeeder::class);
        }

        $categories = Category::where('category_type', 'license')->get();

        $graphicsSoftwareCategory = $categories->first(fn($category) => $category->name === 'Graphics Software');
        $officeSoftwareCategory = $categories->first(fn($category) => $category->name === 'Office Software');

        if (!$graphicsSoftwareCategory) {
            Log::info('Graphics Software category not created. Using random category for seeding.');
            $graphicsSoftwareCategory = Category::inRandomOrder()->first();
        }

        if (!$officeSoftwareCategory) {
            Log::info('Office Software category not created. Using random category for seeding.');
            $officeSoftwareCategory = Category::inRandomOrder()->first();
        }

        if (! Supplier::count()) {
            $this->call(SupplierSeeder::class);
        }

        $supplierIds = Supplier::all()->pluck('id');

        $admin = User::where('permissions->superuser', '1')->first() ?? User::factory()->firstAdmin()->create();

        License::factory()->count(1)->photoshop()->create([
            'category_id' => $graphicsSoftwareCategory->id,
            'supplier_id' => $supplierIds->random(),
            'created_by' => $admin->id,
        ]);

        License::factory()->count(1)->acrobat()->create([
            'category_id' => $officeSoftwareCategory->id,
            'supplier_id' => $supplierIds->random(),
            'created_by' => $admin->id,
        ]);

        License::factory()->count(1)->indesign()->create([
            'category_id' => $graphicsSoftwareCategory->id,
            'supplier_id' => $supplierIds->random(),
            'created_by' => $admin->id,
        ]);

        License::factory()->count(1)->office()->create([
            'category_id' => $officeSoftwareCategory->id,
            'supplier_id' => $supplierIds->random(),
            'created_by' => $admin->id,
        ]);
    }
}
