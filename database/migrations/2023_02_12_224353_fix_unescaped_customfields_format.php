<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\CustomField;

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

        foreach($customfields as $customfield){
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
