<?php

use App\Models\CustomField;
use Illuminate\Database\Migrations\Migration;

class OneMoreMacAddrFix extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach (CustomField::all() as $custom_field) {
            if ($custom_field->format == 'regex:/^MAC$/') {
                $custom_field->format = 'regex:/^[a-fA-F0-9]{2}:[a-fA-F0-9]{2}:[a-fA-F0-9]{2}:[a-fA-F0-9]{2}:[a-fA-F0-9]{2}:[a-fA-F0-9]{2}$/';
            }
            $custom_field->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
