<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReportTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('report_templates', function (Blueprint $table) {
            $table->id();
            $table->integer('created_by')->nullable();
            $table->string('name');

            /*
             * The "options" column was originally json but this causes issues
             * with older versions of mariadb so it was changed text.
             *
             * A follow-up migration definitively changes it to a text column
             * for the systems that had successfully run the migration:
             * 2025_01_06_210534_change_report_templates_options_to_column_text_field.
             *
             * https://github.com/grokability/snipe-it/issues/16015
             */
            $table->text('options');

            $table->softDeletes();
            $table->timestamps();
            $table->index('created_by');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('report_templates');
    }
}
