<?php

namespace App\Importer;

use App\Models\Consumable;

class ConsumableImporter extends ItemImporter
{
    public function __construct($filename)
    {
        parent::__construct($filename);
    }

    protected function handle($row)
    {
        parent::handle($row);
        $this->createConsumableIfNotExists($row);
    }

    /**
     * Create a consumable if a duplicate does not exist
     *
     * @author Daniel Melzter
     * @param  array $row CSV Row Being parsed.
     * @since 3.0
     */
    public function createConsumableIfNotExists($row)
    {
        $consumable = Consumable::where('name', trim($this->item['name']))->first();
        if ($consumable) {

            if (! $this->updating) {
                $this->log('A matching Consumable '.$this->item['name'].' already exists.  ');
                return;
            }
            $this->log('Updating Consumable');
            $consumable->update($this->sanitizeItemForUpdating($consumable));
            $consumable->save();

            return;
        }

        $this->log('No matching consumable, creating one');
        $consumable = new Consumable();
        $consumable->created_by = auth()->id();
        $consumable->fill($this->sanitizeItemForStoring($consumable));

        // This sets an attribute on the Loggable trait for the action log
        $consumable->setImported(true);
        if ($consumable->save()) {
            $this->log('Consumable '.$this->item['name'].' was created');

            return;
        }
        $this->logError($consumable, 'Consumable');
    }
}
