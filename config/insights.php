<?php

declare(strict_types=1);

use NunoMaduro\PhpInsights\Domain\Insights\ForbiddenDefineFunctions;
use NunoMaduro\PhpInsights\Domain\Insights\ForbiddenFinalClasses;
use NunoMaduro\PhpInsights\Domain\Insights\ForbiddenNormalClasses;
use NunoMaduro\PhpInsights\Domain\Insights\ForbiddenPrivateMethods;
use NunoMaduro\PhpInsights\Domain\Insights\ForbiddenTraits;
use NunoMaduro\PhpInsights\Domain\Metrics\Architecture\Classes;
use SlevomatCodingStandard\Sniffs\Commenting\UselessFunctionDocCommentSniff;
use SlevomatCodingStandard\Sniffs\Namespaces\AlphabeticallySortedUsesSniff;
use SlevomatCodingStandard\Sniffs\TypeHints\DeclareStrictTypesSniff;
use SlevomatCodingStandard\Sniffs\TypeHints\DisallowMixedTypeHintSniff;
use SlevomatCodingStandard\Sniffs\TypeHints\ParameterTypeHintSniff;
use SlevomatCodingStandard\Sniffs\TypeHints\PropertyTypeHintSniff;
use SlevomatCodingStandard\Sniffs\TypeHints\ReturnTypeHintSniff;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Preset
    |--------------------------------------------------------------------------
    |
    | This option controls the default preset that will be used by PHP Insights
    | to make your code reliable, simple, and clean. However, you can always
    | adjust the `Metrics` and `Insights` below in this configuration file.
    |
    | Supported: "default", "laravel", "symfony", "magento2", "drupal"
    |
    */

    'preset' => 'laravel',

    /*
    |--------------------------------------------------------------------------
    | IDE
    |--------------------------------------------------------------------------
    |
    | This options allow to add hyperlinks in your terminal to quickly open
    | files in your favorite IDE while browsing your PhpInsights report.
    |
    | Supported: "textmate", "macvim", "emacs", "sublime", "phpstorm",
    | "atom", "vscode".
    |
    | If you have another IDE that is not in this list but which provide an
    | url-handler, you could fill this config with a pattern like this:
    |
    | myide://open?url=file://%f&line=%l
    |
    */

    'ide' => null,

    /*
    |--------------------------------------------------------------------------
    | Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may adjust all the various `Insights` that will be used by PHP
    | Insights. You can either add, remove or configure `Insights`. Keep in
    | mind that all added `Insights` must belong to a specific `Metric`.
    |
    */

    'exclude' => [
        //  'path/to/directory-or-file'
    ],

    'add' => [
        Classes::class => [
            ForbiddenFinalClasses::class,
        ],
    ],

    'remove' => [
        AlphabeticallySortedUsesSniff::class,
        DeclareStrictTypesSniff::class,
        DisallowMixedTypeHintSniff::class,
        ForbiddenDefineFunctions::class,
        ForbiddenNormalClasses::class,
        ForbiddenTraits::class,
        ParameterTypeHintSniff::class,
        PropertyTypeHintSniff::class,
        ReturnTypeHintSniff::class,
        UselessFunctionDocCommentSniff::class,
    ],

    'config' => [
        ForbiddenPrivateMethods::class => [
            'title' => 'The usage of private methods is not idiomatic in Laravel.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Requirements
    |--------------------------------------------------------------------------
    |
    | Here you may define a level you want to reach per `Insights` category.
    | When a score is lower than the minimum level defined, then an error
    | code will be returned. This is optional and individually defined.
    |
    */

    'requirements' => [
//        'min-quality' => 0,
//        'min-complexity' => 0,
//        'min-architecture' => 0,
//        'min-style' => 0,
//        'disable-security-check' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Threads
    |--------------------------------------------------------------------------
    |
    | Here you may adjust how many threads (core) PHPInsights can use to perform
    | the analyse. This is optional, don't provide it and the tool will guess
    | the max core number available. It accepts null value or integer > 0.
    |
    */

    'threads' => null,

];
