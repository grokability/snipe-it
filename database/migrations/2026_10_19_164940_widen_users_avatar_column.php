<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Widen users.avatar from varchar(191) to TEXT. SSO providers
     * (Google Workspace in particular) hand back avatar URLs that
     * exceed 191 characters, which MySQL in non-strict mode silently
     * truncates on write. The truncated URL then 400s on fetch and
     * renders as a broken image. The column isn't indexed, so no
     * key-length concern. Issue #19620.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('avatar')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('avatar', 191)->nullable()->change();
        });
    }
};
