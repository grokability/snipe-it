<?php

declare(strict_types=1);

use Boundwize\StructArmed\Architecture;
use Boundwize\StructArmed\Preset\Preset;
use Boundwize\StructArmed\Preset\Presets\CodeQualityPreset;
use Boundwize\StructArmed\Preset\Presets\Psr4Preset;

return Architecture::define()
    ->skip([
        Psr4Preset::CLASSES_MUST_MATCH_COMPOSER => [
            // multiple classes on single file, can be re-check later
            __DIR__.'/app/Console/Commands',
            __DIR__.'/app/Models',
        ],
        // to enable later for easier to review
        CodeQualityPreset::ANONYMOUS_FUNCTIONS_MUST_BE_STATIC,
    ])
    ->withPresets(Preset::PSR4(), Preset::CODEQUALITY())
    ->layer('Enums', 'app/Enums')
    ->layer('Traits', 'app/Traits')
    ->layer('Jobs', 'app/Jobs')
    ->layer('Models', 'app/Models')
    ->layer('Helpers', 'app/Helpers')
    ->layer('Events', 'app/Events')
    ->layer('Observers', 'app/Observers')
    ->layer('Policies', 'app/Policies')
    ->layer('Exceptions', 'app/Exceptions')
    ->layer('Presenters', 'app/Presenters')
    ->layer('Mail', 'app/Mail')
    ->layer('Notifications', 'app/Notifications')
    ->layer('View', 'app/View')
    ->layer('Services', 'app/Services')
    ->layer('Auth', 'app/Auth')
    ->layer('SyncAdapters', 'app/SyncAdapters')
    ->layer('Rules', 'app/Rules')
    ->layer('Livewire', 'app/Livewire')
    ->layer('Actions', 'app/Actions')
    ->layer('Importer', 'app/Importer')
    ->layer('Listeners', 'app/Listeners')
    ->layer('Console', 'app/Console')
    ->layer('Providers', 'app/Providers')
    ->layer('HttpKernel', 'app/Http/Kernel.php')
    ->layer('Middleware', 'app/Http/Middleware')
    ->layer('HttpTraits', 'app/Http/Traits')
    ->layer('Requests', 'app/Http/Requests')
    ->layer('Transformers', 'app/Http/Transformers')
    ->layer('ApiControllers', 'app/Http/Controllers/Api')
    ->layer('Controllers', 'app/Http/Controllers', 'app/Http/Controllers/Api')
    ->ruleset([
        'Enums' => [],
        'Traits' => [],
        'Jobs' => [],
        'Helpers' => ['Models'],
        'Events' => ['Models'],
        'Observers' => ['Models'],
        'Policies' => ['Models'],
        'Exceptions' => ['Enums', '+Helpers'],
        'Presenters' => ['Enums', '+Helpers'],
        'Mail' => ['+Helpers'],
        'Notifications' => ['+Helpers'],
        'View' => ['+Helpers'],
        'Services' => ['+Events', '+Helpers'],
        'Auth' => ['Services'],
        'SyncAdapters' => ['Models', 'Rules'],
        'Rules' => ['+Helpers', 'SyncAdapters'],
        'Models' => ['+Exceptions', '+Presenters', '+Rules', 'Events', 'HttpTraits', 'Notifications'],
        'Livewire' => ['+Helpers', 'Rules'],
        'Actions' => ['Enums', 'Exceptions', 'Models', 'Notifications'],
        'Importer' => ['+Events', 'Exceptions', 'Notifications'],
        'Listeners' => ['+Events', 'Actions', 'Enums', 'Mail', 'Notifications'],
        'Console' => ['+Events', '+Mail', '+Notifications', 'Enums', 'SyncAdapters'],
        'Providers' => ['+Observers', '+Policies', 'Auth', 'Exceptions', 'Listeners', 'Services', 'View'],
        'HttpKernel' => ['Middleware'],
        'Middleware' => ['+Helpers'],
        'HttpTraits' => ['+Helpers', 'Controllers', 'Requests'],
        'Requests' => ['+Helpers', 'HttpTraits', 'Importer', 'Rules'],
        'Transformers' => ['+Helpers', 'Controllers', 'Enums'],
        'ApiControllers' => ['+Actions', '+Events', '+HttpTraits', '+Middleware', '+Transformers', '+View', 'Rules', 'Traits'],
        'Controllers' => ['+Actions', '+HttpTraits', '+Mail', '+Observers', '+Rules', '+Services', '+View', 'Traits'],
    ]);
