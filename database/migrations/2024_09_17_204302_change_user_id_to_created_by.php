<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->add_to_table_list() as $add_table) {
            if (!Schema::hasColumn($add_table, 'created_by')) {
                Schema::table($add_table, function (Blueprint $add_table) {
                    $add_table->unsignedBigInteger('created_by')->nullable()->before('created_at');
                });
            }
        }

        foreach ($this->existing_table_list() as $table) {
            if (Schema::hasColumn($table, 'user_id')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->renameColumn('user_id', 'created_by');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ($this->add_to_table_list() as $add_table) {
            if (Schema::hasColumn($add_table, 'created_by')) {
                Schema::table($add_table, function (Blueprint $add_table) {
                    $add_table->dropColumn('created_by');
                });
            }
        }

        foreach ($this->existing_table_list() as $table) {
            if (Schema::hasColumn($table, 'created_by')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->renameColumn('created_by', 'user_id');
                });
            }
        }
    }

    public function existing_table_list() {
        return [
            'accessories',
            'accessories_checkout',
            'action_logs',
            'asset_maintenances',
            'assets',
            'categories',
            'components',
            'components_assets',
            'consumables',
            'consumables_users',
            'custom_fields',
            'custom_fieldsets',
            'departments',
            'depreciations',
            'license_seats',
            'licenses',
            'locations',
            'manufacturers',
            'models',
            'settings',
            'status_labels',
            'suppliers',
            'users',
        ];
    }

    public function add_to_table_list() {
        return [
            'companies',
            'imports',
            'kits',
            'kits_accessories',
            'kits_consumables',
            'kits_licenses',
            'kits_models',
            'users_groups',
        ];
    }
};
