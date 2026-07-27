<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Department;
use App\Models\User;
use Database\Seeders\Concerns\ReportsMemory;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UserSeeder extends Seeder
{
    use ReportsMemory;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::truncate();

        // Truncate the company_user pivot too. Truncating users alone leaves
        // orphaned pivot rows referencing the old user_ids; on the next reseed
        // AUTO_INCREMENT hands out those same ids again, and any attempt to
        // reattach a re-created user to the same company hits the
        // (company_id, user_id) unique constraint.
        DB::table('company_user')->truncate();

        if (! Company::count()) {
            $this->call(CompanySeeder::class);
        }

        $companyIds = Company::all()->pluck('id');

        if (! Department::count()) {
            $this->call(DepartmentSeeder::class);
        }

        $departmentIds = Department::all()->pluck('id');

        // Named admins get multiple companies. They manage assets across several organisations.
        foreach (['firstAdmin', 'snipeAdmin', 'testAdmin'] as $state) {
            $user = User::factory()->{$state}()->withoutCompany()->create([
                'department_id' => $departmentIds->random(),
            ]);
            $ids = $companyIds->random(min(rand(2, 3), $companyIds->count()))->toArray();
            $user->companies()->sync($ids);
            $user->syncLegacyCompanyIdMirror();
        }

        // Non-admin resource managers, one per checkoutable resource type.
        // These have full control over their section but no superuser flag,
        // so they show the "what does a scoped operator actually see" view
        // that is useful for docs screenshots. One company each.
        foreach (['assetManager', 'licenseManager', 'accessoryManager', 'consumableManager', 'componentManager', 'userManager'] as $state) {
            $user = User::factory()->{$state}()->withoutCompany()->create([
                'department_id' => $departmentIds->random(),
            ]);
            $user->companies()->sync([$companyIds->random()]);
            $user->syncLegacyCompanyIdMirror();
        }

        // Superusers, one company each.
        User::factory()->count(3)->superuser()
            ->withoutCompany()
            ->state(new Sequence(fn () => [
                'department_id' => $departmentIds->random(),
            ]))
            ->create()
            ->each(function (User $user) use ($companyIds) {
                $user->companies()->sync([$companyIds->random()]);
                $user->syncLegacyCompanyIdMirror();
            });

        // Admins, one company each.
        User::factory()->count(3)->admin()
            ->withoutCompany()
            ->state(new Sequence(fn () => [
                'department_id' => $departmentIds->random(),
            ]))
            ->create()
            ->each(function (User $user) use ($companyIds) {
                $user->companies()->sync([$companyIds->random()]);
                $user->syncLegacyCompanyIdMirror();
            });

        // Regular users, three groups:
        //   ~30% (600) no company
        //   ~50% (1000) one company
        //   ~20% (400) two or three companies
        //
        // Chunked so we don't hold 1000-2000 User models in memory at once
        // (each ->each() / foreach that follows a big ->create() otherwise
        // materializes the whole collection). Reduced demo-server memory
        // pressure that was pushing seeding into swap.
        $chunk = 200;

        $departmentState = fn () => new Sequence(fn () => [
            'department_id' => $departmentIds->random(),
        ]);

        $this->reportMemory('UserSeeder start of regular-user batches');

        memory_reset_peak_usage();
        for ($i = 0; $i < 600 / $chunk; $i++) {
            User::factory()->count($chunk)->viewAssets()
                ->withoutCompany()
                ->state($departmentState())
                ->create();
            gc_collect_cycles();
        }
        $this->reportMemory('UserSeeder after 600 no-company users (chunked)');

        memory_reset_peak_usage();
        for ($i = 0; $i < 1000 / $chunk; $i++) {
            User::factory()->count($chunk)->viewAssets()
                ->withoutCompany()
                ->state($departmentState())
                ->create()
                ->each(function (User $user) use ($companyIds) {
                    $user->companies()->sync([$companyIds->random()]);
                    $user->syncLegacyCompanyIdMirror();
                });
            gc_collect_cycles();
        }
        $this->reportMemory('UserSeeder after 1000 one-company users (chunked)');

        memory_reset_peak_usage();
        for ($i = 0; $i < 400 / $chunk; $i++) {
            User::factory()->count($chunk)->viewAssets()
                ->withoutCompany()
                ->state($departmentState())
                ->create()
                ->each(function (User $user) use ($companyIds) {
                    $ids = $companyIds->random(min(rand(2, 3), $companyIds->count()))->toArray();
                    $user->companies()->sync($ids);
                    $user->syncLegacyCompanyIdMirror();
                });
            gc_collect_cycles();
        }
        $this->reportMemory('UserSeeder after 400 multi-company users (chunked)');

        $src = public_path('/img/demo/avatars/');
        $dst = 'avatars'.'/';
        $del_files = Storage::files($dst);

        foreach ($del_files as $del_file) { // iterate files
            $file_to_delete = str_replace($src, '', $del_file);
            Log::debug('Deleting: '.$file_to_delete);
            try {
                Storage::disk('public')->delete($dst.$del_file);
            } catch (\Exception $e) {
                Log::debug($e);
            }
        }

        $add_files = glob($src.'/*.*');
        foreach ($add_files as $add_file) {
            $file_to_copy = str_replace($src, '', $add_file);
            Log::debug('Copying: '.$file_to_copy);
            try {
                Storage::disk('public')->put($dst.$file_to_copy, file_get_contents($src.$file_to_copy));
            } catch (\Exception $e) {
                Log::debug($e);
            }
        }

        $users = User::orderBy('id', 'asc')->take(20)->get();
        $file_number = 1;

        foreach ($users as $user) {

            $user->avatar = $file_number.'.jpg';
            $user->save();
            $file_number++;
        }

        $this->reportMemory('UserSeeder end (all users + avatars complete)');
    }
}
