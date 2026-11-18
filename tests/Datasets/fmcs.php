<?php

use App\Models\Company;
use App\Models\User;

dataset('FMCS data', [
    function () {
        $jedi = Company::factory()->create();
        $luke = User::factory()->forCompany($jedi)
            ->createAccessories()
            ->createAssets()
            ->createComponents()
            ->createConsumables()
            ->createLicenses()
            ->create();

        return [
            'actor' => $luke,
            'company_attempting_to_associate' => $jedi,
            'assertions' => function ($model) use ($jedi) {
                // Luke submits his own company (Jedi) — it is in his pivot so it is honoured.
                self::assertEquals($jedi->id, $model->company_id);
            },
        ];
    },

    function () {
        $userInNoCompany = User::factory()
            ->withoutCompany()
            ->createAccessories()
            ->createAssets()
            ->createComponents()
            ->createConsumables()
            ->createLicenses()
            ->create();

        return [
            'actor' => $userInNoCompany,
            'company_attempting_to_associate' => Company::factory()->create(),
            'assertions' => function ($model) {
                self::assertNull($model->company_id);
            },
        ];
    },

    function () {
        $superUser = User::factory()->superuser()->withoutCompany()->create();
        $company = Company::factory()->create();

        return [
            'actor' => $superUser,
            'company_attempting_to_associate' => $company,
            'assertions' => function ($model) use ($company) {
                self::assertEquals($model->company_id, $company->id);
            },
        ];
    },
]);
