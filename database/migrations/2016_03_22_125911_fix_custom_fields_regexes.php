<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class FixCustomFieldsRegexes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach (\App\Models\CustomField::all() as $custom_field) {
            switch ($custom_field->format) {

                case '[a-zA-Z]*':
                    $custom_field->format = 'ALPHA';
                    break;

                case '[0-9]*':
                    $custom_field->format = 'NUMERIC';
                    break;

                case '([01]?\\d\\d?|2[0-4]\\d|25[0-5])\\.([01]?\\d\\d?|2[0-4]\\d|25[0-5])\\.([01]?\\d\\d?|2[0-4]\\d|25[0-5])\\.([01]?\\d\\d?|2[0-4]\\d|25[0-5])':
                    $custom_field->format = 'IP';
                    break;

                //ANYTHING ELSE.
                default:
                    $custom_field->format = 'regex:/^'.$custom_field->format.'$/';
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
