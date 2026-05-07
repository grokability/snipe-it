<?php

use App\Models\CustomField;
use Illuminate\Database\Migrations\Migration;

class FixUnescapedCustomfieldsFormat extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $customfields = CustomField::where('format', 'LIKE', '%&%')->get();

        foreach ($customfields as $customfield) {
            $customfield->update(['format' => html_entity_decode($customfield->format)]);
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
