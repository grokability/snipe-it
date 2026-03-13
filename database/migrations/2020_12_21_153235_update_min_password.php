<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateMinPassword extends Migration
{
    /**
     * This migration solves the issue of settings with a minimum password requirement
     * that is below the actual Snipe-IT minimum requirement in v5 (min 5 became min 8).
     *
     * Even though we documented the change in all of the v5 releases, we were still
     * running into issues where admins did not update their password minimum length
     * and could not save settings elsewhere, and would not see a warning.
     *
     * @todo Loosen up the model level validation for the Settings model and rely on
     * FormRequests where it makes more sense. Having a form that returns no useful
     * errors is a bad design pattern.
     *
     * @return void
     */
    public function up()
    {
        App\Models\Setting::where('pwd_secure_min', '<', '8')
            ->update(['pwd_secure_min' => '8']);
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
