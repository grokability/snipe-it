<?php

namespace App\Console\Commands;

use App\Models\Asset;
use Illuminate\Console\Command;
use Illuminate\Support\MessageBag;

class ValidateAssets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'snipeit:validate-assets {--all : Display the valid assets in your table output as well} ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This runs through the list of assets and checks for any validation errors that would prevent it from being updated or checked in or out. ';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $showAll = (bool) $this->option('all');

        $assets = Asset::query()
            ->whereNull('deleted_at')
            ->with('model')
            ->orderBy('assets.created_at', 'desc')
            ->get();

        if (! $showAll) {
            $this->info('Run this command with the --all option to see the full list in the console.');
        }

        $rows = $assets
            ->filter(fn (Asset $asset) => $showAll || ! $asset->isValid())
            ->map(fn (Asset $asset) => [
                trans('general.id') => $asset->id,
                trans('admin/hardware/form.tag') => $asset->asset_tag,
                trans('admin/hardware/form.serial') => $asset->serial ?? '',
                trans('admin/hardware/form.model') => $asset->model?->name ?? '',
                trans('general.model_no') => $asset->model?->model_number ?? '',
                trans('general.error') => $asset->isValid() ? '√ valid' : $this->formatValidationErrors($asset),
            ])
            ->values()
            ->all();

        $this->table(
            [
                trans('general.id'),
                trans('admin/hardware/form.tag'),
                trans('admin/hardware/form.serial'),
                trans('admin/hardware/form.model'),
                trans('general.model_no'),
                trans('general.error'),
            ],
            $rows
        );

        return self::SUCCESS;
    }

    private function formatValidationErrors(Asset $asset): string
    {
        $errors = $asset->getErrors();
        $messages = [];

        if ($errors instanceof MessageBag) {
            $messages = $errors->all();
        } elseif (is_array($errors)) {
            $messages = $errors;
        } else {
            $messages = [(string) $errors];
        }

        $prefixedMessages = collect($messages)
            ->map(fn ($message) => trim((string) $message))
            ->filter()
            ->map(fn (string $message) => str_starts_with($message, '✘') ? $message : '✘ '.$message)
            ->values()
            ->all();

        return implode(PHP_EOL, $prefixedMessages);
    }
}
