<?php

namespace App\Importer;

use App\Events\CheckoutableCheckedOut;
use App\Models\Consumable;
use App\Models\User;

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
     *
     * @param  array  $row  CSV Row Being parsed.
     *
     * @since 3.0
     */
    public function createConsumableIfNotExists($row)
    {
        $consumable = Consumable::where('name', trim($this->item['name']))->first();
        if ($consumable) {

            if (! $this->updating) {
                $this->log('A matching Consumable '.$this->item['name'].' already exists.  ');

                $this->maybeCheckoutConsumable($consumable);

                return;
            }
            $this->log('Updating Consumable');
            $consumable->update($this->sanitizeItemForUpdating($consumable));
            // update() already saves the model, no need to call save() again while Model::unguard() is active
            $consumable->setImported(true);

            $this->maybeCheckoutConsumable($consumable);

            return;
        }

        $this->log('No matching consumable, creating one');
        $consumable = new Consumable;
        $consumable->created_by = auth()->id();
        $consumable->fill($this->sanitizeItemForStoring($consumable));

        // This sets an attribute on the Loggable trait for the action log
        $consumable->setImported(true);
        if ($consumable->save()) {
            $this->log('Consumable '.$this->item['name'].' was created');

            $this->maybeCheckoutConsumable($consumable);

            return;
        }
        $this->logError($consumable, 'Consumable');
    }

    /**
     * Consumables can only be checked out to users (schema constraint -
     * consumables_users.assigned_to is a plain FK to users, no
     * assigned_type). Silently skip if the CSV row's checkout target
     * resolves to a Location or Asset. Attaches a single unit and fires
     * CheckoutableCheckedOut so the actionlog / notification path
     * matches a UI-driven checkout.
     */
    private function maybeCheckoutConsumable(Consumable $consumable): void
    {
        $target = $this->item['checkout_target'] ?? null;
        if (! $target || ! ($target instanceof User)) {
            return;
        }

        if ($consumable->numRemaining() < 1) {
            $this->log('Consumable '.$consumable->name.' has no free units - skipping checkout');

            return;
        }

        if (! $consumable->canCheckoutTo($target)) {
            $this->log(trans('general.error_checkout_company_mismatch', [
                'item' => trans('general.consumable').' "'.$consumable->name.'"',
                'item_company' => $consumable->company?->name ?? trans('general.unassigned'),
                'target' => ($target->name ?? $target->username ?? $target->id),
            ]));

            return;
        }

        $consumable->users()->attach($consumable->id, [
            'consumable_id' => $consumable->id,
            'created_by' => $this->created_by,
            'assigned_to' => $target->id,
            'assigned_type' => User::class,
            'note' => 'Checkout from CSV Importer',
        ]);

        event(new CheckoutableCheckedOut(
            $consumable,
            $target,
            auth()->user(),
            'Checkout from CSV Importer',
            [],
            1,
            false,
        ));

        $this->log('Consumable '.$consumable->name.' checked out to '.($target->username ?? $target->id));
    }
}
