<?php

namespace Database\Seeders;

use App\Models\CustomField;
use App\Models\CustomFieldset;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Log;

class CustomFieldsStandaloneSeeder extends Seeder
{
    public function run(): void
    {
        if (!filter_var(env('CF_ENABLED', true), FILTER_VALIDATE_BOOLEAN)) {
            return;
        }

        if (filter_var(env('CF_DROP_LEGACY', true), FILTER_VALIDATE_BOOLEAN) && Schema::hasTable('assets')) {
            $conn = DB::connection();
            $inTx = method_exists($conn, 'transactionLevel') ? $conn->transactionLevel() > 0 : false;

            if ($inTx) {
                Log::warning('CF_DROP_LEGACY übersprungen: aktive Transaktion erkannt.');
            } else {
                $cols = DB::getSchemaBuilder()->getColumnListing('assets');
                foreach ($cols as $c) {
                    if (strpos($c, '_snipeit_') !== false) {
                        Schema::table('assets', function (Blueprint $t) use ($c) {
                            $t->dropColumn($c);
                        });
                    }
                }
                try {
                    DB::statement('ALTER TABLE assets ROW_FORMAT=DYNAMIC');
                } catch (\Throwable $e) {
                }
            }
        }


        if (filter_var(env('CF_RESET', true), FILTER_VALIDATE_BOOLEAN)) {
            if (Schema::hasTable('custom_field_custom_fieldset')
                && Schema::hasTable('custom_fields')
                && Schema::hasTable('custom_fieldsets')) {
                DB::statement('SET FOREIGN_KEY_CHECKS=0');
                DB::table('custom_field_custom_fieldset')->truncate();
                CustomField::truncate();
                CustomFieldset::truncate();
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
            }
        }

        $counts = json_decode(env('CF_SETS', '[]'), true) ?: [];
        $counts = array_values(array_unique(array_map('intval', $counts)));
        sort($counts);
        if (empty($counts)) {
            return;
        }
        $max    = (int) end($counts);
        $prefix = env('CF_FIELDSET_PREFIX', 'cfset_');

        $pool = []; 
        for ($i = 1; $i <= $max; $i++) {
            $name  = "CF_FIELD_{$i}"; 
            $field = CustomField::firstOrCreate(
                ['name' => $name],
                [
                    'element' => 'textarea',
                    'format'  => '',
                    'help_text' => "Auto field {$i}",
                    'auto_add_to_fieldsets' => 0,
                    'show_in_requestable_list' => 0,
                ]
            );
            $pool[$i] = $field->id;
        }

        foreach ($counts as $count) {
            $fieldsetName = "{$prefix}{$count}";
            $fs = CustomFieldset::firstOrCreate(['name' => $fieldsetName]);

            $rows = [];
            for ($i = 1; $i <= $count; $i++) {
                $fid = $pool[$i];
                $exists = DB::table('custom_field_custom_fieldset')
                    ->where('custom_field_id', $fid)
                    ->where('custom_fieldset_id', $fs->id)
                    ->exists();

                if (!$exists) {
                    $rows[] = [
                        'custom_field_id'    => $fid,
                        'custom_fieldset_id' => $fs->id,
                        'order'              => $i - 1,
                        'required'           => 0,
                    ];
                }
            }

            foreach (array_chunk($rows, 500) as $chunk) {
                DB::table('custom_field_custom_fieldset')->insert($chunk);
            }
        }
    }
}
