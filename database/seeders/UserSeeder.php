<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::truncate();

        if (! Company::count()) {
            $this->call(CompanySeeder::class);
        }

        $companyIds = Company::all()->pluck('id');

        if (! Department::count()) {
            $this->call(DepartmentSeeder::class);
        }

        $departmentIds = Department::all()->pluck('id');

        // Named admins get multiple companies — they manage assets across several organisations.
        foreach (['firstAdmin', 'snipeAdmin', 'testAdmin'] as $state) {
            $user = User::factory()->{$state}()->create([
                'company_id' => null,
                'department_id' => $departmentIds->random(),
            ]);
            $ids = $companyIds->random(min(rand(2, 3), $companyIds->count()))->toArray();
            User::where('id', $user->id)->update(['company_id' => $ids[0]]);
            $user->companies()->sync($ids);
        }

        // Superusers — one company each.
        User::factory()->count(3)->superuser()
            ->state(new Sequence(fn ($sequence) => [
                'company_id' => $companyIds->random(),
                'department_id' => $departmentIds->random(),
            ]))
            ->create();

        // Admins — one company each.
        User::factory()->count(3)->admin()
            ->state(new Sequence(fn ($sequence) => [
                'company_id' => $companyIds->random(),
                'department_id' => $departmentIds->random(),
            ]))
            ->create();

        // Regular users — three groups:
        //   ~30 % (600)  no company
        //   ~50 % (1 000) one company
        //   ~20 % (400)  two or three companies

        User::factory()->count(600)->viewAssets()
            ->state(new Sequence(fn ($sequence) => [
                'company_id' => null,
                'department_id' => $departmentIds->random(),
            ]))
            ->create();

        User::factory()->count(1000)->viewAssets()
            ->state(new Sequence(fn ($sequence) => [
                'company_id' => $companyIds->random(),
                'department_id' => $departmentIds->random(),
            ]))
            ->create();

        $multiCompanyUsers = User::factory()->count(400)->viewAssets()
            ->state(new Sequence(fn ($sequence) => [
                'company_id' => null,
                'department_id' => $departmentIds->random(),
            ]))
            ->create();

        foreach ($multiCompanyUsers as $user) {
            $ids = $companyIds->random(min(rand(2, 3), $companyIds->count()))->toArray();
            User::where('id', $user->id)->update(['company_id' => $ids[0]]);
            $user->companies()->sync($ids);
        }

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

    }
}
