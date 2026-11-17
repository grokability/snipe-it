<?php

namespace Database\Factories;

use App\Enums\FileStorage;
use App\Models\Import;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\Support\Importing;

/**
 * @extends Factory<Import>
 */
class ImportFactory extends Factory
{
    /**
     * {@inheritdoc}
     */
    protected $model = Import::class;

    /**
     * {@inheritdoc}
     */
    public function definition()
    {
        return [
            'name' => $this->faker->company,
            'file_path' => Str::random().'.csv',
            'filesize' => $this->faker->randomDigitNotNull(),
            'field_map' => null,
            // In production `store()` always sets `created_by = auth()->id()`,
            // so an import never exists without an owner. Mirror that here:
            // if the test is already actingAs someone, use them; otherwise
            // mint a fresh user. This keeps the "actor owns their import"
            // idiom that existing tests already assume, and lets the API
            // owner-scope on read/process paths match production behavior.
            'created_by' => auth()->id() ?? User::factory(),
        ];
    }

    /**
     * Create an accessory import type.
     *
     * @return static
     */
    public function accessory()
    {
        return $this->state(function (array $attributes) {
            $fileBuilder = Importing\AccessoriesImportFileBuilder::new();

            $attributes['name'] = "{$attributes['name']} Accessories";
            $attributes['import_type'] = 'accessory';
            $attributes['header_row'] = $fileBuilder->toCsv()[0];
            $attributes['first_row'] = $fileBuilder->firstRow();

            return $attributes;
        });
    }

    /**
     * Create an asset import type.
     *
     * @return static
     */
    public function asset()
    {
        return $this->state(function (array $attributes) {
            $fileBuilder = Importing\AssetsImportFileBuilder::new();

            $attributes['name'] = "{$attributes['name']} Assets";
            $attributes['import_type'] = 'asset';
            $attributes['header_row'] = $fileBuilder->toCsv()[0];
            $attributes['first_row'] = $fileBuilder->firstRow();

            return $attributes;
        });
    }

    /**
     * Create a component import type.
     *
     * @return static
     */
    public function component()
    {
        return $this->state(function (array $attributes) {
            $fileBuilder = Importing\ComponentsImportFileBuilder::new();

            $attributes['name'] = "{$attributes['name']} Components";
            $attributes['import_type'] = 'component';
            $attributes['header_row'] = $fileBuilder->toCsv()[0];
            $attributes['first_row'] = $fileBuilder->firstRow();

            return $attributes;
        });
    }

    /**
     * Create a consumable import type.
     *
     * @return static
     */
    public function consumable()
    {
        return $this->state(function (array $attributes) {
            $fileBuilder = Importing\ConsumablesImportFileBuilder::new();

            $attributes['name'] = "{$attributes['name']} Consumables";
            $attributes['import_type'] = 'consumable';
            $attributes['header_row'] = $fileBuilder->toCsv()[0];
            $attributes['first_row'] = $fileBuilder->firstRow();

            return $attributes;
        });
    }

    /**
     * Create a license import type.
     *
     * @return static
     */
    public function license()
    {
        return $this->state(function (array $attributes) {
            $fileBuilder = Importing\LicensesImportFileBuilder::new();

            $attributes['name'] = "{$attributes['name']} Licenses";
            $attributes['import_type'] = 'license';
            $attributes['header_row'] = $fileBuilder->toCsv()[0];
            $attributes['first_row'] = $fileBuilder->firstRow();

            return $attributes;
        });
    }

    /**
     * Create a users import type.
     *
     * @return static
     */
    public function users()
    {
        return $this->state(function (array $attributes) {
            $fileBuilder = Importing\UsersImportFileBuilder::new();

            $attributes['name'] = "{$attributes['name']} Employees";
            $attributes['import_type'] = 'user';
            $attributes['header_row'] = $fileBuilder->toCsv()[0];
            $attributes['first_row'] = $fileBuilder->firstRow();

            return $attributes;
        });
    }

    /**
     * Create an asset model import type.
     *
     * @return static
     */
    public function assetmodel()
    {
        return $this->state(function (array $attributes) {
            $fileBuilder = Importing\AssetModelsImportFileBuilder::new();

            $attributes['name'] = "{$attributes['name']} Asset Model";
            $attributes['import_type'] = 'assetModel';
            $attributes['header_row'] = $fileBuilder->toCsv()[0];
            $attributes['first_row'] = $fileBuilder->firstRow();

            return $attributes;
        });
    }

    /**
     * Create a supplier import type.
     *
     * @return static
     */
    public function suppliers()
    {
        return $this->state(function (array $attributes) {
            $fileBuilder = Importing\SuppliersImportFileBuilder::new();
            $attributes['name'] = "Supplier {$attributes['name']}";
            $attributes['import_type'] = 'supplier';
            $attributes['header_row'] = $fileBuilder->toCsv()[0];
            $attributes['first_row'] = $fileBuilder->firstRow();

            return $attributes;
        });
    }

    /**
     * Create an supplier import type.
     *
     * @return static
     */
    public function locations()
    {
        return $this->state(function (array $attributes) {
            $fileBuilder = Importing\SuppliersImportFileBuilder::new();
            $attributes['name'] = "Location {$attributes['name']}";
            $attributes['import_type'] = 'location';
            $attributes['header_row'] = $fileBuilder->toCsv()[0];
            $attributes['first_row'] = $fileBuilder->firstRow();

            return $attributes;
        });
    }

    /**
     * Create a supplier import type.
     *
     * @return static
     */
    public function manufacturers()
    {
        return $this->state(function (array $attributes) {
            $fileBuilder = Importing\ManufacturersImportFileBuilder::new();
            $attributes['name'] = "Manufacturer {$attributes['name']}";
            $attributes['import_type'] = 'manufacturer';
            $attributes['header_row'] = $fileBuilder->toCsv()[0];
            $attributes['first_row'] = $fileBuilder->firstRow();

            return $attributes;
        });
    }

    public function categories()
    {
        return $this->state(function (array $attributes) {
            $fileBuilder = Importing\CategoriesImportFileBuilder::new();
            $attributes['name'] = "Category {$attributes['name']}";
            $attributes['import_type'] = 'category';
            $attributes['header_row'] = $fileBuilder->toCsv()[0];
            $attributes['first_row'] = $fileBuilder->firstRow();

            return $attributes;
        });
    }

    public function assetHistory()
    {
        return $this->state(function (array $attributes) {
            $fileBuilder = Importing\AssetHistoryImportFileBuilder::new();
            $attributes['name'] = "{$attributes['name']} Asset History";
            $attributes['import_type'] = 'assetHistory';
            $attributes['header_row'] = $fileBuilder->toCsv()[0];
            $attributes['first_row'] = $fileBuilder->firstRow();

            return $attributes;
        });
    }

    /**
     * Lay down a trivial CSV at the generated file_path so tests that
     * hit ItemImportRequest::import() don't 500 on the "file missing
     * on disk" guard added for S3-backed private disks. Pre-S3, the
     * importer silently treated a missing path as empty CSV content;
     * the guard now returns an error early, so permission-style tests
     * (which only care that the endpoint doesn't 403) need an actual
     * file behind the Import row. CleansUpImportFiles tears it back
     * down afterApplicationDestroyed.
     */
    public function withStubFile(): static
    {
        return $this->afterCreating(function (Import $import) {
            // Header-only CSV: zero data rows means no per-row
            // importer validation runs, so the request returns 200
            // regardless of import type. Enough to prove the file is
            // reachable - the dedicated per-type tests that assert
            // actual row shape use ImportFileBuilder for real content.
            $storedPath = FileStorage::Imports->privateStorageKey().$import->file_path;
            Storage::put($storedPath, "col\n");
        });
    }
}
