<?php

namespace App\Importer;

use App\Models\Category;
use Illuminate\Support\Facades\Log;

/**
 * When we are importing users via an Asset/etc import, we use createOrFetchUser() in
 * Importer\Importer.php. [ALG]
 *
 * Class CategoryImporter
 */
class CategoryImporter extends ItemImporter
{
    protected $categories;

    public function __construct($filename)
    {
        parent::__construct($filename);
    }

    protected function handle($row)
    {
        parent::handle($row);
        $this->createCategoryIfNotExists($row);
    }

    /**
     * Create a category if a duplicate does not exist.
     * @todo Investigate how this should interact with Importer::createCategoryIfNotExists
     *
     * @author A. Gianotto
     * @since 6.1.0
     * @param array $row
     */
    public function createCategoryIfNotExists(array $row)
    {

        $editingCategory = false;

        $category = Category::where('name', '=', $this->findCsvMatch($row, 'name'))->first();

        if ($this->findCsvMatch($row, 'id')!='') {
            // Override category if an ID was given
            \Log::debug('Finding category by ID: '.$this->findCsvMatch($row, 'id'));
            $category = Category::find($this->findCsvMatch($row, 'id'));
        }


        if ($category) {
            if (! $this->updating) {
                $this->log('A matching Category '.$this->item['name'].' already exists');
                return;
            }

            $this->log('Updating Category');
            $editingCategory = true;
        } else {
            $this->log('No Matching Category, Create a new one');
            $category = new Category;
            $category->created_by = auth()->id();
        }

        // Pull the records from the CSV to determine their values
        $this->item['name'] = trim($this->findCsvMatch($row, 'name'));
        $this->item['notes'] = trim($this->findCsvMatch($row, 'notes'));
        $this->item['eula_text'] = trim($this->findCsvMatch($row, 'eula_text'));
        $this->item['category_type'] = trim(strtolower($this->findCsvMatch($row, 'category_type')));
        $this->item['use_default_eula'] = trim(($this->fetchHumanBoolean($this->findCsvMatch($row, 'use_default_eula'))) == 1) ? 1 : 0;
        $this->item['require_acceptance'] = trim(($this->fetchHumanBoolean($this->findCsvMatch($row, 'require_acceptance'))) == 1) ? 1 : 0;
        $this->item['checkin_email'] = trim(($this->fetchHumanBoolean($this->findCsvMatch($row, 'checkin_email'))) == 1) ? 1 : 0;


        Log::debug('Item array is: ');
        Log::debug(print_r($this->item, true));


        if ($editingCategory) {
            Log::debug('Updating existing category');
            $category->update($this->sanitizeItemForUpdating($category));
        } else {
            Log::debug('Creating category');
            $category->fill($this->sanitizeItemForStoring($category));
        }

        if ($category->save()) {
            $this->log('Category '.$category->name.' created or updated from CSV import');
            return $category;

        } else {
            Log::debug($category->getErrors());
            $this->logError($category, 'Category "'.$this->item['name'].'"');
            return $category->errors;
        }


    }
}