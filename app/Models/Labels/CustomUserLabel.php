<?php

namespace App\Models\Labels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class CustomUserLabel extends Model
{
    use SoftDeletes;

    protected $table = 'custom_user_labels';

    protected $fillable = [
        'name',
        'base_label',
        'type',
        'overrides',
        'config_snapshot',
        'is_default',
    ];

    protected $casts = [
        'overrides' => 'array',
        'config_snapshot' => 'array',
    ];

    /**
     * Builds a lookup of base label templates for instantiation.
     * Example:
     * [
     *     'L4736_A' => App\Models\Labels\Sheets\Avery\L4736_A::class,
     *     'DefaultLabel' => App\Models\Labels\DefaultLabel::class,
     * ]
     */
    public static function availableBaseLabels(): array
    {
        $namespaceRoot = 'App\\Models\\Labels\\';
        $basePath = app_path('Models/Labels');

        $allowedRoots = [
            $basePath.'/Sheets',
            $basePath.'/Tapes',
            $basePath.'/DefaultLabel.php',
        ];

        $labels = [];

        foreach ($allowedRoots as $root) {
            if (is_file($root)) {
                $files = [new \SplFileInfo($root)];
            } else {
                $files = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($root)
                );
            }

            foreach ($files as $file) {
                if (! $file->isFile() || $file->getExtension() !== 'php') {
                    continue;
                }

                $fullPath = $file->getPathname();
                $relativePath = Str::after($fullPath, $basePath.DIRECTORY_SEPARATOR);

                $class = $namespaceRoot.str_replace(
                    [DIRECTORY_SEPARATOR, '.php'],
                    ['\\', ''],
                    $relativePath
                );

                if (! class_exists($class)) {
                    continue;
                }

                $labels[class_basename($class)] = $class;
            }
        }

        ksort($labels);

        return $labels;
    }

    /**
     * Creates a base label instance from a template.
     */
    public static function makeBaseLabel(?string $template): ?object
    {
        if (! $template) {
            return null;
        }

        $available = static::availableBaseLabels();
        $templateKey = class_basename(str_replace('\\', '/', $template));

        $class = $available[$templateKey] ?? null;

        if (! $class || ! class_exists($class)) {
            return null;
        }

        return new $class;
    }

    /**
     * Returns the differences between the final config and base config.
     *
     * Only includes values that have been added or overridden.
     *
     * @param  array<string, mixed>  $finalConfig
     * @param  array<string, mixed>  $baseConfig
     * @return array<string, mixed>
     */
    public static function diffEditorConfig(array $finalConfig, array $baseConfig): array
    {
        $diff = [];

        foreach ($finalConfig as $key => $value) {
            $hasBaseKey = array_key_exists($key, $baseConfig);
            $baseValue = $hasBaseKey ? $baseConfig[$key] : null;

            if (is_array($value) && is_array($baseValue)) {
                $nestedDiff = static::diffEditorConfig($value, $baseValue);

                if (! empty($nestedDiff)) {
                    $diff[$key] = $nestedDiff;
                }

                continue;
            }

            if (! $hasBaseKey) {
                $diff[$key] = static::normalizeDiffValue($value);

                continue;
            }

            if (static::valuesDiffer($value, $baseValue)) {
                $diff[$key] = static::normalizeDiffValue($value);
            }
        }

        return $diff;
    }

    /**
     * Determines if values differ for override detection, applying a tolerance
     * to prevent insignificant numeric differences from being treated as changes.
     */
    protected static function valuesDiffer($value, $baseValue, float $epsilon = 0.001): bool
    {
        if (is_bool($value) || is_bool($baseValue)) {
            return (bool) $value !== (bool) $baseValue;
        }

        if (is_numeric($value) && is_numeric($baseValue)) {
            return abs((float) $value - (float) $baseValue) > $epsilon;
        }

        return $value !== $baseValue;
    }

    /**
     * Normalizes values for diff output, rounding numerics to reduce noise
     * from minor precision differences.
     */
    protected static function normalizeDiffValue($value)
    {
        if (is_numeric($value)) {
            return round((float) $value, 3);
        }

        return $value;
    }
}
