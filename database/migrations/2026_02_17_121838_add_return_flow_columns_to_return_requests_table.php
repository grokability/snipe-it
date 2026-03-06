<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('return_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('return_requests', 'closed_at')) {
                $table->timestamp('closed_at')->nullable()->after('received_at');
            }

            if (!Schema::hasColumn('return_requests', 'checked_in_at')) {
                $table->timestamp('checked_in_at')->nullable()->after('closed_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('return_requests', function (Blueprint $table) {
            if (Schema::hasColumn('return_requests', 'checked_in_at')) {
                $table->dropColumn('checked_in_at');
            }
            if (Schema::hasColumn('return_requests', 'closed_at')) {
                $table->dropColumn('closed_at');
            }
        });
    }
};

