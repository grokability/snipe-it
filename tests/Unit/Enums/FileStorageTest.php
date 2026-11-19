<?php

use App\Enums\FileStorage;
use App\Http\Controllers\Controller;

test('public only cases expose public paths and not private', function () {
    expect(FileStorage::Avatars->publicPath())->toBe('avatars/');
    expect(FileStorage::Avatars->publicDir())->toBe('public/uploads/avatars');
    expect(FileStorage::Avatars->privatePath())->toBeNull();
    expect(FileStorage::Avatars->privateDir())->toBeNull();
});

test('private only cases expose private paths and not public', function () {
    expect(FileStorage::Signatures->privatePath())->toBe('signatures/');
    expect(FileStorage::Signatures->privateDir())->toBe('storage/private_uploads/signatures');
    expect(FileStorage::Signatures->publicPath())->toBeNull();
    expect(FileStorage::Signatures->publicDir())->toBeNull();
});

test('both scope cases expose both paths', function () {
    // Assets split content across both scopes: public holds asset
    // pictures, private holds asset file attachments.
    expect(FileStorage::Assets->publicPath())->toBe('assets/');
    expect(FileStorage::Assets->privatePath())->toBe('assets/');
    expect(FileStorage::Assets->publicDir())->toBe('public/uploads/assets');
    expect(FileStorage::Assets->privateDir())->toBe('storage/private_uploads/assets');
});

test('public dirs enumeration includes barcodes cache', function () {
    // Barcode cache dir has to be in the public enumeration so the
    // restore prunes stale QR / 1D barcode images that would
    // otherwise linger from the previous install.
    expect(FileStorage::publicDirs())->toContain('public/uploads/barcodes');
});

test('private dirs enumeration includes historical aliases', function () {
    // Backups from older installs may carry old subdir names.
    // The restore maps them to their current destinations so the
    // files land correctly after the rename.
    $privateDirs = FileStorage::privateDirs();

    expect($privateDirs)->toHaveKey('storage/private_uploads/assetmodels');
    expect($privateDirs['storage/private_uploads/assetmodels'])->toBe('storage/private_uploads/models');

    expect($privateDirs)->toHaveKey('storage/private_uploads/asset_maintenances');
    expect($privateDirs['storage/private_uploads/asset_maintenances'])->toBe('storage/private_uploads/maintenances');
});

test('public dirs includes every public scoped case', function () {
    $publicDirs = FileStorage::publicDirs();
    foreach (FileStorage::cases() as $case) {
        if ($case->hasPublicScope()) {
            expect($publicDirs)->toContain($case->publicDir()); // "Public dir enumeration missing {$case->value}."
        }
    }
});

test('private dirs includes every private scoped case', function () {
    $privateDirs = FileStorage::privateDirs();
    foreach (FileStorage::cases() as $case) {
        if ($case->hasPrivateScope()) {
            expect($privateDirs)->toContain($case->privateDir()); // Private dir enumeration missing {$case->value}.
        }
    }
});

test('backups resolve to spatie dir not private uploads tree', function () {
    expect(FileStorage::Backups->hasPrivateScope())->toBeTrue();
    expect(FileStorage::Backups->hasPublicScope())->toBeFalse();
    expect(FileStorage::Backups->privateDir())->toBe('storage/app/backups');
    expect(FileStorage::Backups->publicDir())->toBeNull();
});

test('backups appear in full private dirs enumeration', function () {
    // publicDirs() / privateDirs() are the authoritative "every dir
    // of that scope" list. Backups has to be here so a future caller
    // enumerating private storage locations sees it.
    expect(FileStorage::privateDirs())->toContain('storage/app/backups');
});

test('restore filtered enumerations exclude backups', function () {
    // The restore pruning passes must leave Spatie's backup directory
    // alone. Otherwise the archive being read from, plus every
    // sibling rollback point, would be wiped mid-restore.
    expect(FileStorage::privateDirsForRestore())->not->toContain('storage/app/backups');
    expect(FileStorage::publicDirsForRestore())->not->toContain('storage/app/backups');
});

test('restore filtered private enumeration still contains regular private cases', function () {
    $pruneable = FileStorage::privateDirsForRestore();
    expect($pruneable)->toContain('storage/private_uploads/signatures');
    expect($pruneable)->toContain('storage/private_uploads/users');
    expect($pruneable)->toContain('storage/private_uploads/eula-pdfs');

    // Historical alias entries carry through as string-keyed pairs
    // because older archives still ship the old subdir names.
    expect($pruneable)->toHaveKey('storage/private_uploads/assetmodels');
});

test('private storage key resolves to default disk key with trailing slash', function () {
    expect(FileStorage::Signatures->privateStorageKey())->toBe('private_uploads/signatures/');
    expect(FileStorage::Users->privateStorageKey())->toBe('private_uploads/users/');
    expect(FileStorage::EulaPdfs->privateStorageKey())->toBe('private_uploads/eula-pdfs/');
    expect(FileStorage::Accessories->privateStorageKey())->toBe('private_uploads/accessories/');
});

test('private storage key special cases backups outside private uploads', function () {
    // Backups live at storage/app/backups (Spatie's convention), not
    // under storage/app/private_uploads, so the default-disk key has
    // no private_uploads/ prefix.
    expect(FileStorage::Backups->privateStorageKey())->toBe('backups/');
});

test('private storage key is null for public only cases', function () {
    expect(FileStorage::Avatars->privateStorageKey())->toBeNull();
    expect(FileStorage::Barcodes->privateStorageKey())->toBeNull();
});

test('model class resolves each case to its eloquent owner', function () {
    expect(FileStorage::Accessories->modelClass())->toBe(\App\Models\Accessory::class);
    expect(FileStorage::Assets->modelClass())->toBe(\App\Models\Asset::class);
    expect(FileStorage::Categories->modelClass())->toBe(\App\Models\Category::class);
    expect(FileStorage::Companies->modelClass())->toBe(\App\Models\Company::class);
    expect(FileStorage::Components->modelClass())->toBe(\App\Models\Component::class);
    expect(FileStorage::Consumables->modelClass())->toBe(\App\Models\Consumable::class);
    expect(FileStorage::Departments->modelClass())->toBe(\App\Models\Department::class);
    expect(FileStorage::Licenses->modelClass())->toBe(\App\Models\License::class);
    expect(FileStorage::Locations->modelClass())->toBe(\App\Models\Location::class);
    expect(FileStorage::Maintenances->modelClass())->toBe(\App\Models\Maintenance::class);
    expect(FileStorage::Manufacturers->modelClass())->toBe(\App\Models\Manufacturer::class);
    expect(FileStorage::Suppliers->modelClass())->toBe(\App\Models\Supplier::class);
});

test('model class maps rename cases to their owner', function () {
    // Avatars is the public case for the User model's avatar column,
    // Users is the private case for User's Files-tab uploads. Both
    // tie back to User despite the two case names.
    expect(FileStorage::Avatars->modelClass())->toBe(\App\Models\User::class);
    expect(FileStorage::Users->modelClass())->toBe(\App\Models\User::class);

    // Models is the public case for the AssetModel model's image.
    expect(FileStorage::Models->modelClass())->toBe(\App\Models\AssetModel::class);
});

test('every mapped storage path resolves to a private key', function () {
    // Regression test for #19808. Suppliers / Companies / Departments
    // appear in Controller::getMapStoragePath() for file attachments
    // but were missing from FileStorage::hasPrivateScope(), so
    // privateStorageKey() returned null and UploadedFilesController
    // crashed with a TypeError when uploading. Pin the invariant
    // that every entry in the controller map produces a usable path.
    foreach (Controller::getMapStoragePath() as $resource => $path) {
        expect($path)->toBeString("No private storage key for '$resource'. Add HasUploads to the model, or add the case to FileStorage::NON_MODEL_PRIVATE_CASES.");
        $this->assertNotSame('', $path);
    }
});

test('every has uploads model resolves to a private file storage case', function () {
    // Walk every HasUploads model and confirm its FileStorage case is marked private.
    $hasUploadsModels = [];
    foreach (FileStorage::cases() as $case) {
        $model = $case->modelClass();
        if ($model === null) {
            continue;
        }
        if (in_array(\App\Models\Traits\HasUploads::class, class_uses_recursive($model), true)) {
            $hasUploadsModels[$model] = $case;
        }
    }

    expect($hasUploadsModels)->not->toBeEmpty('No HasUploads models mapped through FileStorage::modelClass(). Did the mapping drift?');

    foreach ($hasUploadsModels as $model => $case) {
        expect($case->hasPrivateScope())->toBeTrue("Model $model uses HasUploads but FileStorage::{$case->name}->hasPrivateScope() returned false.");
        expect($case->privateStorageKey())->toBeString();
    }
});

test('model class is null for non model owned cases', function () {
    // Signatures, EULA PDFs, audits, imports, backups, and the
    // barcode cache do not belong to a single parent model, so
    // modelClass() returns null for all of them. Callers iterating
    // cases to build a model->path map skip these.
    expect(FileStorage::Audits->modelClass())->toBeNull();
    expect(FileStorage::Backups->modelClass())->toBeNull();
    expect(FileStorage::Barcodes->modelClass())->toBeNull();
    expect(FileStorage::EulaPdfs->modelClass())->toBeNull();
    expect(FileStorage::Imports->modelClass())->toBeNull();
    expect(FileStorage::Signatures->modelClass())->toBeNull();
});
