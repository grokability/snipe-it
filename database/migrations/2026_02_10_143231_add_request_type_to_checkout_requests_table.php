<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('checkout_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('checkout_requests', 'request_type')) {
                $table->string('request_type')->default('checkout')->after('id');
            }

            if (!Schema::hasColumn('checkout_requests', 'return_status')) {
                $table->string('return_status')->nullable()->after('request_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('checkout_requests', function (Blueprint $table) {
            if (Schema::hasColumn('checkout_requests', 'request_type')) {
                $table->dropColumn('request_type');
            }

            if (Schema::hasColumn('checkout_requests', 'return_status')) {
                $table->dropColumn('return_status');
            }
        });
    }
};

