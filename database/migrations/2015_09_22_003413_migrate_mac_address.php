<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class MigrateMacAddress extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //DB::getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');

        $f2 = new \App\Models\CustomFieldset(['name' => 'Asset with MAC Address']);
        $f2->timestamps = false; //when this model was first created, it had no timestamps. But later on it gets them.
        if (! $f2->save()) {
            throw new Exception("couldn't save customfieldset");
        }
        $macid = DB::table('custom_fields')->insertGetId([
            'name' => 'MAC Address',
            'format' => \App\Models\CustomField::PREDEFINED_FORMATS['MAC'],
            'element'=>'text', ]);
        if (! $macid) {
            throw new Exception("Can't save MAC Custom field: $macid");
        }

        $f2->fields()->attach($macid, ['required' => false, 'order' => 1]);
        \App\Models\AssetModel::where(['show_mac_address' => true])->update(['fieldset_id'=>$f2->id]);

        Schema::table('assets', function (Blueprint $table) {
            $table->renameColumn('mac_address', '_snipeit_mac_address');
        });

        // DB::statement("ALTER TABLE assets CHANGE mac_address _snipeit_mac_address varchar(255)");

        $ans = Schema::table('models', function (Blueprint $table) {
            $table->renameColumn('show_mac_address', 'deprecated_mac_address');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $f = \App\Models\CustomFieldset::where(['name' => 'Asset with MAC Address'])->first();

        if ($f) {
            $f->fields()->delete();
            $f->delete();
        }

        Schema::table('models', function (Blueprint $table) {
            $table->renameColumn('deprecated_mac_address', 'show_mac_address');
        });

        if (Schema::hasColumn('assets', '_snipeit_mac_address')) {
            DB::statement('ALTER TABLE assets CHANGE _snipeit_mac_address mac_address varchar(255)');
        }
    }
}
