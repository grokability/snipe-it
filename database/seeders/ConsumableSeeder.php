<?php

namespace Database\Seeders;

use App\Models\Consumable;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConsumableSeeder extends Seeder
{
    public function run()
    {
        Consumable::truncate();
        DB::table('consumables_users')->truncate();

        $admin = User::where('permissions->superuser', '1')->first() ?? User::factory()->firstAdmin()->create();

        Consumable::factory()->count(1)->cardstock()->create(['created_by' => $admin->id]);
        Consumable::factory()->count(1)->paper()->create(['created_by' => $admin->id]);
        Consumable::factory()->count(1)->ink()->create(['created_by' => $admin->id]);

        // Check out a couple of each consumable to random users so the
        // view page doesn't render as an empty checkout list.
        $checkoutTargets = User::where('activated', 1)
            ->where('show_in_list', '!=', '0')
            ->inRandomOrder()
            ->limit(6)
            ->get();
        foreach (Consumable::all() as $consumable) {
            foreach ($checkoutTargets->random(min(rand(2, 4), $checkoutTargets->count())) as $user) {
                $consumable->users()->attach($user->id, [
                    'created_by' => $admin->id,
                ]);
            }
        }
    }
}
