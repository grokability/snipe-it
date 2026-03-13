<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class FixBadAssignedToIds extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        DB::update('update '.DB::getTablePrefix().'assets SET assigned_to=NULL where assigned_to=0');

        Schema::table('status_labels', function ($table) {
            $table->boolean('deployable')->default(0);
            $table->boolean('pending')->default(0);
            $table->boolean('archived')->default(0);
            $table->text('notes')->nullable();
        });

        DB::table('status_labels')->insert([
            ['user_id' => 1, 'name' => 'Pending', 'deployable' => 0, 'pending' => 1, 'archived' => 0, 'notes' => 'These assets are not yet ready to be deployed, usually because of configuration or waiting on parts.'],
            ['user_id' => 1, 'name' => 'Ready to Deploy', 'deployable' => 1, 'pending' => 0, 'archived' => 0, 'notes' => 'These assets are ready to deploy.'],
            ['user_id' => 1, 'name' => 'Archived', 'deployable' => 0, 'pending' => 0, 'archived' => 1, 'notes' => 'These assets are no longer in circulation or viable.'],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::table('status_labels', function ($table) {
            $table->dropColumn('deployable');
            $table->dropColumn('pending');
            $table->dropColumn('archived');
            $table->dropColumn('notes');
        });
    }
}
