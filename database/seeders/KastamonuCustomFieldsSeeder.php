<?php

namespace Database\Seeders;

use App\Models\CustomField;
use App\Models\CustomFieldset;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KastamonuCustomFieldsSeeder extends Seeder
{
    public function run(): void
    {
        // Create the fieldset
        $fieldset = CustomFieldset::firstOrCreate(
            ['name' => 'Fabrika Ekipman Bilgileri']
        );

        $this->command->info("Fieldset: {$fieldset->name} (ID: {$fieldset->id})");

        // Define fields: [name, element, format, field_values, required]
        $fields = [
            [
                'name'         => 'Kalibrasyon Tarihi',
                'element'      => 'text',
                'format'       => 'DATE',
                'field_values' => null,
                'required'     => false,
                'help_text'    => 'Son kalibrasyon tarihi',
            ],
            [
                'name'         => 'Sonraki Bakım Tarihi',
                'element'      => 'text',
                'format'       => 'DATE',
                'field_values' => null,
                'required'     => false,
                'help_text'    => 'Planlanan bakım tarihi',
            ],
            [
                'name'         => 'Bakım Periyodu (Gün)',
                'element'      => 'text',
                'format'       => 'NUMERIC',
                'field_values' => null,
                'required'     => false,
                'help_text'    => 'Bakımlar arası gün sayısı',
            ],
            [
                'name'         => 'Saha Lokasyonu',
                'element'      => 'radio',
                'format'       => 'ANY',
                'field_values' => "Hat 1\nHat 2\nHat 3\nDepo\nOfis\nBakım Atölyesi",
                'required'     => false,
                'help_text'    => null,
            ],
            [
                'name'         => 'Kalibrasyon Sorumlusu',
                'element'      => 'text',
                'format'       => 'ANY',
                'field_values' => null,
                'required'     => false,
                'help_text'    => 'Kalibrasyondan sorumlu kişi',
            ],
            [
                'name'         => 'Ekipman Durumu',
                'element'      => 'radio',
                'format'       => 'ANY',
                'field_values' => "Çalışıyor\nBakımda\nArızalı\nDepo",
                'required'     => false,
                'help_text'    => null,
            ],
        ];

        foreach ($fields as $order => $fieldData) {
            $field = CustomField::firstOrCreate(
                ['name' => $fieldData['name']],
                [
                    'element'          => $fieldData['element'],
                    'format'           => $fieldData['format'],
                    'field_values'     => $fieldData['field_values'],
                    'field_encrypted'  => false,
                    'show_in_listview' => true,
                    'show_in_email'    => true,
                    'help_text'        => $fieldData['help_text'],
                ]
            );

            // Attach to fieldset if not already attached (bypass pivot_order ordering issue)
            $alreadyAttached = DB::table('custom_field_custom_fieldset')
                ->where('custom_fieldset_id', $fieldset->id)
                ->where('custom_field_id', $field->id)
                ->exists();

            if (!$alreadyAttached) {
                $fieldset->fields()->attach($field->id, [
                    'required' => $fieldData['required'] ? 1 : 0,
                    'order'    => $order + 1,
                ]);
            }

            $this->command->info("  [{$field->id}] {$field->name} ({$field->element}) -> {$field->db_column}");
        }

        $fieldCount = DB::table('custom_field_custom_fieldset')
            ->where('custom_fieldset_id', $fieldset->id)
            ->count();

        $this->command->info('');
        $this->command->info("Done. {$fieldCount} fields attached to \"{$fieldset->name}\".");
        $this->command->info('Assign this fieldset to an Asset Model via Admin > Asset Models > Edit.');
    }
}
