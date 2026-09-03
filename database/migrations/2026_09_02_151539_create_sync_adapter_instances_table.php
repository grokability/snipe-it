<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sync_adapter_instances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->string('slug', 191)->unique();
            $table->string('adapter_type', 191)->index();
            $table->string('label', 191);
            $table->boolean('active')->default(true);
            $table->boolean('built_in')->default(false);
            $table->timestamp('last_synced_at')->nullable()->index();
            $table->text('last_sync_result')->nullable();
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->timestamps();
        });

        // Seed the well-known adapter.
        $now = now();
        DB::table('sync_adapter_instances')->insert(collect([
            'fleet' => 'Fleet',
            'addigy' => 'Addigy',
            'intune' => 'Microsoft Intune',
            'jamf' => 'Jamf Pro',
            'jamf_school' => 'Jamf School',
            'kandji' => 'Kandji',
            'unifi' => 'UniFi',
            'meraki_sm' => 'Meraki Systems Manager',
            'mosyle' => 'Mosyle',
            'osctrl' => 'osctrl',
            'workspace_one' => 'Omnissa Workspace ONE',
            'zentral' => 'Zentral',
            'jumpcloud' => 'JumpCloud',
        ])->map(fn ($label, $type) => [
            'slug' => $type,
            'adapter_type' => $type,
            'label' => $label,
            'active' => false,
            'built_in' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ])->values()->all());
    }

    public function down(): void
    {
        Schema::dropIfExists('sync_adapter_instances');
    }
};
