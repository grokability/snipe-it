<?php

namespace Database\Seeders;

use App\Models\CustomField;
use App\Models\CustomFieldset;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CustomFieldSeeder extends Seeder
{
    public function run()
    {
        $columns = DB::getSchemaBuilder()->getColumnListing('assets');

        foreach ($columns as $column) {
            if (strpos($column, '_snipeit_') !== false) {
                Schema::table('assets', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }
        CustomField::truncate();
        CustomFieldset::truncate();
        DB::table('custom_field_custom_fieldset')->truncate();

        CustomFieldset::factory()->count(1)->mobile()->create();
        CustomFieldset::factory()->count(1)->computer()->create();
        CustomField::factory()->count(1)->imei()->create();
        CustomField::factory()->count(1)->phone()->create();
        CustomField::factory()->count(1)->ram()->create();
        CustomField::factory()->count(1)->cpu()->create();
        CustomField::factory()->count(1)->macAddress()->create();
        CustomField::factory()->count(1)->testEncrypted()->create();
        CustomField::factory()->count(1)->testCheckbox()->create();
        CustomField::factory()->count(1)->testMultiListbox()->create();
        CustomField::factory()->count(1)->testRadio()->create();
        CustomField::factory()->count(1)->testMarkdownTextarea()->create();
        CustomField::factory()->count(1)->testDate()->create();
        CustomField::factory()->count(1)->testDatetime()->create();
        CustomField::factory()->count(1)->xss()->create();
        // Fields the Custom HTTP adapter (and other sync adapters)
        // typically populate. Pre-created so a fresh demo tenant can
        // wire an adapter's mapping table to real fieldset entries
        // without hand-creating them first.
        CustomField::factory()->count(1)->ipAddress()->create();
        CustomField::factory()->count(1)->operatingSystem()->create();
        CustomField::factory()->count(1)->osVersion()->create();
        CustomField::factory()->count(1)->lastCheckIn()->create();
        // Extra fields the ABM adapter's device-level extras map to.
        // Same rationale as ipAddress / lastCheckIn above: pre-created
        // so a demo tenant can wire the mapping table for the ABM
        // Wi-Fi/Bluetooth/Ethernet MACs, storage capacity, cellular
        // identifiers, and Activation Lock status without hand-
        // rolling the target fields first.
        CustomField::factory()->count(1)->storageCapacity()->create();
        CustomField::factory()->count(1)->bluetoothMac()->create();
        CustomField::factory()->count(1)->ethernetMac()->create();
        CustomField::factory()->count(1)->meid()->create();
        CustomField::factory()->count(1)->eid()->create();
        CustomField::factory()->count(1)->activationLock()->create();

        DB::table('custom_field_custom_fieldset')->insert([
            [
                'custom_field_id' => '1',
                'custom_fieldset_id' => '1',
                'order' => 0,
                'required' => 0,
            ],
            [
                'custom_field_id' => '2',
                'custom_fieldset_id' => '1',
                'order' => 0,
                'required' => 0,
            ],
            [
                'custom_field_id' => '3',
                'custom_fieldset_id' => '2',
                'order' => 0,
                'required' => 0,
            ],
            [
                'custom_field_id' => '4',
                'custom_fieldset_id' => '2',
                'order' => 0,
                'required' => 0,
            ],
            [
                'custom_field_id' => '5',
                'custom_fieldset_id' => '2',
                'order' => 0,
                'required' => 0,
            ],

            [
                'custom_field_id' => '6',
                'custom_fieldset_id' => '2',
                'order' => 0,
                'required' => 0,
            ],

            [
                'custom_field_id' => '6',
                'custom_fieldset_id' => '1',
                'order' => 0,
                'required' => 0,
            ],

            [
                'custom_field_id' => '7',
                'custom_fieldset_id' => '2',
                'order' => 0,
                'required' => 0,
            ],
            [
                'custom_field_id' => '7',
                'custom_fieldset_id' => '1',
                'order' => 0,
                'required' => 0,
            ],

            [
                'custom_field_id' => '8',
                'custom_fieldset_id' => '2',
                'order' => 0,
                'required' => 0,
            ],
            [
                'custom_field_id' => '8',
                'custom_fieldset_id' => '1',
                'order' => 0,
                'required' => 0,
            ],

            [
                'custom_field_id' => '9',
                'custom_fieldset_id' => '1',
                'order' => 0,
                'required' => 0,
            ],

            [
                'custom_field_id' => '9',
                'custom_fieldset_id' => '2',
                'order' => 0,
                'required' => 0,
            ],

            [
                'custom_field_id' => '10',
                'custom_fieldset_id' => '1',
                'order' => 0,
                'required' => 0,
            ],

            [
                'custom_field_id' => '11',
                'custom_fieldset_id' => '1',
                'order' => 0,
                'required' => 0,
            ],

            [
                'custom_field_id' => '10',
                'custom_fieldset_id' => '2',
                'order' => 0,
                'required' => 0,
            ],

            [
                'custom_field_id' => '11',
                'custom_fieldset_id' => '2',
                'order' => 0,
                'required' => 0,
            ],

            // ipAddress, operatingSystem, osVersion, lastCheckIn get
            // attached to both fieldsets so admins wiring up any sync
            // adapter can route those pulled values into the same
            // fields whether the device is a laptop / desktop or a
            // phone / tablet. xss (id 12) is intentionally left off.
            [
                'custom_field_id' => '13',
                'custom_fieldset_id' => '1',
                'order' => 0,
                'required' => 0,
            ],
            [
                'custom_field_id' => '13',
                'custom_fieldset_id' => '2',
                'order' => 0,
                'required' => 0,
            ],
            [
                'custom_field_id' => '14',
                'custom_fieldset_id' => '1',
                'order' => 0,
                'required' => 0,
            ],
            [
                'custom_field_id' => '14',
                'custom_fieldset_id' => '2',
                'order' => 0,
                'required' => 0,
            ],
            [
                'custom_field_id' => '15',
                'custom_fieldset_id' => '1',
                'order' => 0,
                'required' => 0,
            ],
            [
                'custom_field_id' => '15',
                'custom_fieldset_id' => '2',
                'order' => 0,
                'required' => 0,
            ],
            [
                'custom_field_id' => '16',
                'custom_fieldset_id' => '1',
                'order' => 0,
                'required' => 0,
            ],
            [
                'custom_field_id' => '16',
                'custom_fieldset_id' => '2',
                'order' => 0,
                'required' => 0,
            ],

            // ABM device-level extras. Storage capacity, Bluetooth MAC,
            // and Activation Lock get attached to both fieldsets since
            // every device (phone or laptop) reports them. Ethernet MAC
            // is computer-only. MEID and EID are cellular-only
            [
                'custom_field_id' => '17',
                'custom_fieldset_id' => '1',
                'order' => 0,
                'required' => 0,
            ],
            [
                'custom_field_id' => '17',
                'custom_fieldset_id' => '2',
                'order' => 0,
                'required' => 0,
            ],
            [
                'custom_field_id' => '18',
                'custom_fieldset_id' => '1',
                'order' => 0,
                'required' => 0,
            ],
            [
                'custom_field_id' => '18',
                'custom_fieldset_id' => '2',
                'order' => 0,
                'required' => 0,
            ],
            [
                'custom_field_id' => '19',
                'custom_fieldset_id' => '2',
                'order' => 0,
                'required' => 0,
            ],
            [
                'custom_field_id' => '20',
                'custom_fieldset_id' => '1',
                'order' => 0,
                'required' => 0,
            ],
            [
                'custom_field_id' => '21',
                'custom_fieldset_id' => '1',
                'order' => 0,
                'required' => 0,
            ],
            [
                'custom_field_id' => '22',
                'custom_fieldset_id' => '1',
                'order' => 0,
                'required' => 0,
            ],
            [
                'custom_field_id' => '22',
                'custom_fieldset_id' => '2',
                'order' => 0,
                'required' => 0,
            ],

        ]);
    }
}
