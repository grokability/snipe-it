<?php

use App\Models\Company;

test('soft deleted company is force deleted by purge', function () {
    $company = Company::factory()->create();
    $company->delete();

    $this->assertSoftDeleted($company);

    $this->artisan('snipeit:purge', ['--force' => 'true'])
        ->assertExitCode(0);

    $this->assertDatabaseMissing('companies', ['id' => $company->id]);
});

test('live company is untouched by purge', function () {
    $company = Company::factory()->create();

    $this->artisan('snipeit:purge', ['--force' => 'true'])
        ->assertExitCode(0);

    $this->assertDatabaseHas('companies', ['id' => $company->id, 'deleted_at' => null]);
});
