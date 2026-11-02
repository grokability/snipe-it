<?php

namespace App\Console\Commands;

use App\Enums\FileStorage;
use App\Models\Actionlog;
use App\Models\CheckoutAcceptance;
use App\Models\Import;
use App\Models\Setting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\info;
use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\note;
use function Laravel\Prompts\progress;
use function Laravel\Prompts\select;
use function Laravel\Prompts\table;
use function Laravel\Prompts\warning;

/**
 * Walk the file-reference graph in both directions and report the
 * mismatches. Two directions:
 *
 *   - missing: DB row points at a file that is not on disk. Broken
 *     references the UI silently falls back from (image placeholder,
 *     empty Files tab download).
 *   - orphan: file on disk that no DB row claims. Dead-weight uploads
 *     left behind by soft-delete + purge, by manual DB edits, or by
 *     older code paths that forgot their delete-hook.
 *
 * Scope map is driven off FileStorage cases:
 *
 *   - MVC Model image + avatar columns (assets, models, accessories,
 *     consumables, components, locations, manufacturers, suppliers,
 *     companies, departments, categories, maintenances, users).
 *     Added to the sweep automatically when a new FileStorage case
 *     gets a modelClass() branch.
 *   - Settings logos (logo, email_logo, label_logo,
 *     acceptance_pdf_logo, favicon).
 *   - action_logs.filename, split per polymorphic item_type so
 *     orphans / broken refs attribute to the owning model.
 *   - imports.file_path.
 *   - checkout_acceptances.signature_filename and stored_eula_file,
 *     the only non-action_log route into FileStorage::Signatures and
 *     FileStorage::EulaPdfs.
 */
class AuditFileStorage extends Command
{
    protected $signature = 'snipeit:audit-file-storage
        {--summary : Show only totals, not the full per-file listing}
        {--json : Emit machine-readable JSON instead of tables}
        {--csv= : Write mismatched rows to a CSV at this path (empty = no CSV)}
        {--chunk=1000 : DB rows fetched per batch when scanning image columns}
        {--only= : Comma-separated list of categories to scan (images, settings, action_logs, imports, acceptances). "images" = per-model image/avatar columns. Default = all.}
        {--direction= : missing (DB->disk), orphan (disk->DB), or both. Prompts interactively if omitted.}
        {--delete : Delete orphan files from disk after the scan. Prompts for confirmation unless --force is passed.}
        {--force : Skip the confirmation prompt when --delete is set. Required for scripted/non-interactive deletes.}';

    protected $description = 'Report file-reference mismatches between DB rows and disk, in either direction.';

    /**
     * Non-default column names for the model-image scan. Any
     * public-scoped FileStorage case whose `modelClass()` resolves
     * contributes a scan target. The column defaults to `image` and
     * is overridden here per case. Keyed off the case name so the
     * mapping is stable even if a case's backing value is renamed.
     */
    private const IMAGE_COLUMN_OVERRIDES = [
        'Avatars' => 'avatar',
    ];

    /**
     * Setting columns that carry uploaded logos or icons. All sit on
     * the public disk at the root w/no prefix.
     */
    private const SETTING_LOGO_COLUMNS = [
        'logo',
        'email_logo',
        'label_logo',
        'acceptance_pdf_logo',
        'favicon',
    ];

    public function handle(): int
    {
        $json = (bool) $this->option('json');
        $direction = $this->parseDirectionOption($json);
        $only = $this->parseOnlyOption($json);
        $summary = (bool) $this->option('summary');
        $csv = $this->option('csv');
        $chunk = max(100, (int) $this->option('chunk'));

        $rows = [];

        if (in_array($direction, ['missing', 'both'], true)) {
            if (! $json) {
                info('Pass 1 of '.($direction === 'both' ? '2' : '1').': DB rows that point at missing files.');
            }
            $rows = array_merge($rows, $this->runMissingPass($only, $chunk));
        }

        if (in_array($direction, ['orphan', 'both'], true)) {
            if (! $json) {
                info('Pass '.($direction === 'both' ? '2 of 2' : '1 of 1').': files on disk that no DB row claims.');
            }
            $rows = array_merge($rows, $this->runOrphanPass($only));
        }

        if ($csv) {
            $this->writeCsv($csv, $rows);
        }

        $deleted = 0;
        if ($this->option('delete')) {
            $deleted = $this->deleteOrphansIfConfirmed($rows, $json);
        }

        if ($json) {
            $payload = [
                'total' => count($rows),
                'by_direction' => $this->tallyByDirection($rows),
                'by_table_type' => $this->tallyByReference($rows),
                'rows' => $summary ? [] : $rows,
            ];
            if ($this->option('delete')) {
                $payload['deleted'] = $deleted;
            }
            $this->line(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        }

        $this->renderReport($rows, $summary);

        if ($this->option('delete')) {
            info("Deleted {$deleted} orphan file(s) from disk.");
        }

        return self::SUCCESS;
    }

    /**
     * Confirm (interactively, or via --force) and delete every orphan
     * row's backing file. Missing rows are left alone because there's
     * nothing on disk to delete for them.
     *
     * @param  array<int, array{direction: string, table: string, type: string, id: int|string, file: string, disk?: string, storage_key?: string}>  $rows
     */
    private function deleteOrphansIfConfirmed(array $rows, bool $json): int
    {
        $orphans = array_values(array_filter($rows, fn (array $row) => $row['direction'] === 'orphan'));

        if ($orphans === []) {
            if (! $json) {
                info('No orphan files to delete.');
            }

            return 0;
        }

        $count = count($orphans);

        if (! $this->option('force') && ! $json) {
            if (! $this->input->isInteractive()) {
                warning('Refusing to delete without --force in a non-interactive context.');

                return 0;
            }

            $confirmed = confirm(
                label: "Delete {$count} orphan file(s) from disk? This cannot be undone.",
                default: false,
            );

            if (! $confirmed) {
                info('Deletion skipped.');

                return 0;
            }
        }

        $bar = progress(label: 'Deleting orphan files', steps: $count);
        $bar->start();

        $deleted = 0;
        foreach ($orphans as $orphan) {
            $disk = $orphan['disk'] ?? null;
            $key = $orphan['storage_key'] ?? null;

            if ($disk === null || $key === null) {
                $bar->advance();

                continue;
            }

            if (Storage::disk($disk)->delete($key)) {
                $deleted++;
            }
            $bar->advance();
        }

        $bar->finish();

        return $deleted;
    }

    /**
     * @param  array<int, string>  $only
     * @return array<int, array{direction: string, table: string, type: string, id: int|string, file: string}>
     */
    private function runMissingPass(array $only, int $chunk): array
    {
        $rows = [];

        if (in_array('images', $only, true)) {
            $rows = array_merge($rows, $this->scanModelImages($chunk));
        }

        if (in_array('settings', $only, true)) {
            $rows = array_merge($rows, $this->scanSettingsLogos());
        }

        if (in_array('action_logs', $only, true)) {
            $rows = array_merge($rows, $this->scanActionLogFilenames($chunk));
        }

        if (in_array('imports', $only, true)) {
            $rows = array_merge($rows, $this->scanImportFiles($chunk));
        }

        if (in_array('acceptances', $only, true)) {
            $rows = array_merge($rows, $this->scanAcceptanceFiles($chunk));
        }

        return array_map(fn (array $row) => ['direction' => 'missing'] + $row, $rows);
    }

    /**
     * Walk the disk, flag files no DB row claims. For each category,
     * build a set of referenced filenames once, then enumerate the
     * directory and bucket anything missing from the set as an orphan.
     *
     * @param  array<int, string>  $only
     * @return array<int, array{direction: string, table: string, type: string, id: int|string, file: string}>
     */
    private function runOrphanPass(array $only): array
    {
        $rows = [];

        if (in_array('images', $only, true)) {
            $rows = array_merge($rows, $this->scanOrphanImages());
        }

        if (in_array('action_logs', $only, true)) {
            $rows = array_merge($rows, $this->scanOrphanActionLogUploads());
        }

        if (in_array('imports', $only, true)) {
            $rows = array_merge($rows, $this->scanOrphanImports());
        }

        if (in_array('acceptances', $only, true)) {
            $rows = array_merge($rows, $this->scanOrphanAcceptances());
        }

        // Settings logos deliberately skipped on the orphan pass. They
        // live at the public disk root alongside non-logo top-level
        // keys (the per-install bucket root on S3, public/uploads/ on
        // local), so disk-enumeration there would false-positive on
        // files written by non-Snipe-IT processes or future features.

        return array_map(fn (array $row) => ['direction' => 'orphan'] + $row, $rows);
    }

    /**
     * Resolve the direction option. CLI flag wins. Interactive tty
     * with no flag + no --json prompts via select(). Everything else
     * falls through to `both`.
     */
    private function parseDirectionOption(bool $json): string
    {
        $raw = (string) $this->option('direction');
        $valid = ['missing', 'orphan', 'both'];

        if ($raw !== '' && in_array($raw, $valid, true)) {
            return $raw;
        }

        if ($raw !== '') {
            warning('Invalid --direction value. Valid: '.implode(', ', $valid).'. Falling back to both.');

            return 'both';
        }

        if (! $json && $this->input->isInteractive()) {
            return (string) select(
                label: 'Which direction?',
                options: [
                    'both' => 'Both (DB <-> disk)',
                    'missing' => 'Missing: DB row -> file not on disk',
                    'orphan' => 'Orphan: file on disk -> no DB row',
                ],
                default: 'both',
            );
        }

        return 'both';
    }

    /**
     * For each public-scoped FileStorage case with a modelClass(), list
     * the files on the public disk under that case's publicPath() and
     * flag anything the model's image/avatar column does not reference.
     * Uses the same column-override map as the missing pass so the two
     * directions scan the exact same column.
     *
     * @return array<int, array{table: string, type: string, id: int|string, file: string}>
     */
    private function scanOrphanImages(): array
    {
        $rows = [];
        $disk = Storage::disk('public');

        foreach ($this->modelImageSources() as [$modelClass, $column, $prefix]) {
            $table = (new $modelClass)->getTable();
            $referenced = array_flip(
                $modelClass::query()
                    ->whereNotNull($column)
                    ->where($column, '!=', '')
                    ->pluck($column)
                    ->map(fn ($v) => (string) $v)
                    ->filter(fn (string $v) => ! str_starts_with($v, 'http://') && ! str_starts_with($v, 'https://') && ! str_starts_with($v, '//'))
                    ->all()
            );

            $files = $disk->files(rtrim($prefix, '/'));
            if ($files === []) {
                continue;
            }

            $bar = progress(label: "Scanning orphan files in {$prefix}", steps: count($files));
            $bar->start();

            $orphans = 0;
            foreach ($files as $filepath) {
                $basename = basename($filepath);
                if (! isset($referenced[$basename])) {
                    $orphans++;
                    $rows[] = [
                        'table' => $table,
                        'type' => $column,
                        'id' => '-',
                        'file' => $this->displayPath('public', $filepath),
                        'disk' => 'public',
                        'storage_key' => $filepath,
                    ];
                }
                $bar->advance();
            }

            $bar->finish();

            note("  {$prefix}: ".number_format(count($files)).' files on disk, '.number_format($orphans).' orphan.');
        }

        return $rows;
    }

    /**
     * For each private FileStorage case whose modelClass() resolves,
     * list the files under its private directory and flag any that
     * action_logs does not reference. One DB query per case, then an
     * in-memory diff against the file list.
     *
     * @return array<int, array{table: string, type: string, id: int|string, file: string}>
     */
    private function scanOrphanActionLogUploads(): array
    {
        $rows = [];

        foreach (FileStorage::cases() as $case) {
            if (! $case->hasPrivateScope() || $case === FileStorage::Backups) {
                continue;
            }

            $modelClass = $case->modelClass();
            if ($modelClass === null) {
                continue;
            }

            $table = (new $modelClass)->getTable();
            $referenced = array_flip(
                Actionlog::query()
                    ->where('item_type', $modelClass)
                    ->whereNotNull('filename')
                    ->where('filename', '!=', '')
                    ->pluck('filename')
                    ->all()
            );

            $prefix = $case->privateStorageKey();
            $files = Storage::files(rtrim($prefix, '/'));
            if ($files === []) {
                continue;
            }

            $bar = progress(label: "Scanning orphan files in {$prefix}", steps: count($files));
            $bar->start();

            $orphans = 0;
            foreach ($files as $filepath) {
                $basename = basename($filepath);
                if (! isset($referenced[$basename])) {
                    $orphans++;
                    $rows[] = [
                        'table' => $table,
                        'type' => 'uploads',
                        'id' => '-',
                        'file' => $this->displayPath(config('filesystems.default'), $filepath),
                        'disk' => config('filesystems.default'),
                        'storage_key' => $filepath,
                    ];
                }
                $bar->advance();
            }

            $bar->finish();

            note("  {$prefix}: ".number_format(count($files)).' files on disk, '.number_format($orphans).' orphan.');
        }

        return $rows;
    }

    /**
     * @return array<int, array{table: string, type: string, id: int|string, file: string}>
     */
    private function scanOrphanImports(): array
    {
        $referenced = array_flip(
            Import::query()
                ->whereNotNull('file_path')
                ->where('file_path', '!=', '')
                ->pluck('file_path')
                ->all()
        );

        $prefix = FileStorage::Imports->privateStorageKey();
        $files = Storage::files(rtrim($prefix, '/'));
        if ($files === []) {
            return [];
        }

        $bar = progress(label: "Scanning orphan files in {$prefix}", steps: count($files));
        $bar->start();

        $rows = [];
        $orphans = 0;
        foreach ($files as $filepath) {
            $basename = basename($filepath);
            if (! isset($referenced[$basename])) {
                $orphans++;
                $rows[] = [
                    'table' => 'imports',
                    'type' => 'file_path',
                    'id' => '-',
                    'file' => $this->displayPath(config('filesystems.default'), $filepath),
                    'disk' => config('filesystems.default'),
                    'storage_key' => $filepath,
                ];
            }
            $bar->advance();
        }

        $bar->finish();

        note("  {$prefix}: ".number_format(count($files)).' files on disk, '.number_format($orphans).' orphan.');

        return $rows;
    }

    /**
     * One referenced-filename set per acceptance file column, then diff
     * against the signatures/ and eula-pdfs/ directories independently.
     *
     * @return array<int, array{table: string, type: string, id: int|string, file: string}>
     */
    private function scanOrphanAcceptances(): array
    {
        $rows = [];

        $signatureRefs = array_flip(
            CheckoutAcceptance::query()
                ->whereNotNull('signature_filename')
                ->where('signature_filename', '!=', '')
                ->pluck('signature_filename')
                ->all()
        );
        $eulaRefs = array_flip(
            CheckoutAcceptance::query()
                ->whereNotNull('stored_eula_file')
                ->where('stored_eula_file', '!=', '')
                ->pluck('stored_eula_file')
                ->all()
        );

        $rows = array_merge($rows, $this->diffAcceptanceDir(
            FileStorage::Signatures->privateStorageKey(),
            'signature_filename',
            $signatureRefs,
        ));
        $rows = array_merge($rows, $this->diffAcceptanceDir(
            FileStorage::EulaPdfs->privateStorageKey(),
            'stored_eula_file',
            $eulaRefs,
        ));

        return $rows;
    }

    /**
     * @param  array<string, int>  $referenced
     * @return array<int, array{table: string, type: string, id: int|string, file: string}>
     */
    private function diffAcceptanceDir(string $prefix, string $column, array $referenced): array
    {
        $files = Storage::files(rtrim($prefix, '/'));
        if ($files === []) {
            return [];
        }

        $bar = progress(label: "Scanning orphan files in {$prefix}", steps: count($files));
        $bar->start();

        $rows = [];
        $orphans = 0;
        foreach ($files as $filepath) {
            $basename = basename($filepath);
            if (! isset($referenced[$basename])) {
                $orphans++;
                $rows[] = [
                    'table' => 'checkout_acceptances',
                    'type' => $column,
                    'id' => '-',
                    'file' => $this->displayPath(config('filesystems.default'), $filepath),
                    'disk' => config('filesystems.default'),
                    'storage_key' => $filepath,
                ];
            }
            $bar->advance();
        }

        $bar->finish();

        note("  {$prefix}: ".number_format(count($files)).' files on disk, '.number_format($orphans).' orphan.');

        return $rows;
    }

    /**
     * @param  array<int, array{direction: string, table: string, type: string, id: int|string, file: string}>  $rows
     * @return array<string, int>
     */
    private function tallyByDirection(array $rows): array
    {
        $tally = ['missing' => 0, 'orphan' => 0];
        foreach ($rows as $row) {
            $tally[$row['direction']] = ($tally[$row['direction']] ?? 0) + 1;
        }

        return $tally;
    }

    /**
     * Scan every model image / avatar column. Rows with a non-empty
     * value get an existence check against the public disk at the
     * scan target's prefix + the column value.
     *
     * @return array<int, array{table: string, type: string, id: int|string, file: string}>
     */
    private function scanModelImages(int $chunk): array
    {
        $missing = [];

        foreach ($this->modelImageSources() as [$modelClass, $column, $prefix]) {
            $table = (new $modelClass)->getTable();
            $total = $modelClass::query()
                ->whereNotNull($column)
                ->where($column, '!=', '')
                ->count();

            if ($total === 0) {
                continue;
            }

            $missCount = 0;
            $bar = progress(label: "Scanning {$table}.{$column}", steps: $total);
            $bar->start();

            $modelClass::query()
                ->select(['id', $column])
                ->whereNotNull($column)
                ->where($column, '!=', '')
                ->chunkById($chunk, function ($rows) use (&$missing, &$missCount, $column, $prefix, $table, $bar): void {
                    foreach ($rows as $row) {
                        $value = (string) $row->{$column};

                        // Skip anything that looks like an external URL
                        // (older installs occasionally stored full
                        // URLs). The exists check would false-negative
                        // on those and pollute the report.
                        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://') || str_starts_with($value, '//')) {
                            $bar->advance();

                            continue;
                        }

                        $relative = $prefix.$value;
                        if (! Storage::disk('public')->exists($relative)) {
                            $missCount++;
                            $missing[] = [
                                'table' => $table,
                                'type' => $column,
                                'id' => $row->id,
                                'file' => $this->displayPath('public', $relative),
                            ];
                        }
                        $bar->advance();
                    }
                });

            $bar->finish();

            note("  {$table}.{$column}: ".number_format($total).' references, '.number_format($missCount).' missing.');
        }

        return $missing;
    }

    /**
     * Build the [modelClass, columnName, storagePrefix] list from the
     * FileStorage enum. Any public-scoped case with a resolvable
     * modelClass() contributes a scan target. Barcodes gets filtered
     * because its modelClass() is null (no owning model), and
     * private-only cases (Audits, Imports, Signatures, EulaPdfs,
     * Backups) stay out because they live on the private disk and have
     * dedicated scanners below. New image-bearing models land here
     * automatically once their FileStorage case + modelClass branch
     * are added, with an entry in IMAGE_COLUMN_OVERRIDES only if the
     * column isn't the default `image`.
     *
     * @return iterable<int, array{0: class-string, 1: string, 2: string}>
     */
    private function modelImageSources(): iterable
    {
        foreach (FileStorage::cases() as $case) {
            $modelClass = $case->modelClass();
            if ($modelClass === null || ! $case->hasPublicScope()) {
                continue;
            }

            yield [
                $modelClass,
                self::IMAGE_COLUMN_OVERRIDES[$case->name] ?? 'image',
                $case->publicPath(),
            ];
        }
    }

    /**
     * The settings singleton has five upload columns. All sit on the
     * public disk at the root (no prefix). Only one row exists.
     *
     * @return array<int, array{table: string, type: string, id: int|string, file: string}>
     */
    private function scanSettingsLogos(): array
    {
        $missing = [];
        $settings = Setting::first();

        if (! $settings) {
            return [];
        }

        $bar = progress(label: 'Scanning settings logos', steps: count(self::SETTING_LOGO_COLUMNS));
        $bar->start();

        foreach (self::SETTING_LOGO_COLUMNS as $column) {
            $value = (string) ($settings->{$column} ?? '');
            if ($value === '') {
                $bar->advance();

                continue;
            }

            if (! Storage::disk('public')->exists($value)) {
                $missing[] = [
                    'table' => 'settings',
                    'type' => $column,
                    'id' => $settings->id,
                    'file' => $this->displayPath('public', $value),
                ];
            }
            $bar->advance();
        }

        $bar->finish();

        note('  settings logos: '.number_format(count(self::SETTING_LOGO_COLUMNS)).' columns checked, '.number_format(count($missing)).' missing.');

        return $missing;
    }

    /**
     * action_logs.filename rows live under a handful of different
     * private disk directories depending on the action_type +
     * item_type combo. Actionlog already knows how to resolve its
     * own path via uploads_file_path(), so we call through that
     * instead of re-implementing the switch here.
     *
     * The check reads from the default disk (PRIVATE_FILESYSTEM_DISK)
     * so S3-backed setups get their private bucket read instead of
     * the local storage_path().
     *
     * @return array<int, array{table: string, type: string, id: int|string, file: string}>
     */
    private function scanActionLogFilenames(int $chunk): array
    {
        $missing = [];
        $total = Actionlog::query()
            ->whereNotNull('filename')
            ->where('filename', '!=', '')
            ->count();

        if ($total === 0) {
            return [];
        }

        $bar = progress(label: 'Scanning Files-tab uploads', steps: $total);
        $bar->start();

        // Scan tally keyed by `<table>.uploads` so admins see orphaned
        // Files-tab attachments charged to the model they belong to
        // (assets.uploads, licenses.uploads, etc) instead of a single
        // action_logs.filename lump. Matches how admins think about
        // the data in the UI.
        $byType = [];

        Actionlog::query()
            ->whereNotNull('filename')
            ->where('filename', '!=', '')
            ->chunkById($chunk, function ($rows) use (&$missing, &$byType, $bar): void {
                foreach ($rows as $log) {
                    $path = $log->uploads_file_path();
                    $table = $this->tableForItemType($log->item_type);

                    if ($table === null || ! $path) {
                        $bar->advance();

                        continue;
                    }

                    $byType[$table] = ($byType[$table] ?? ['total' => 0, 'missing' => 0]);
                    $byType[$table]['total']++;

                    if (! Storage::exists($path)) {
                        $byType[$table]['missing']++;
                        $missing[] = [
                            'table' => $table,
                            'type' => 'uploads',
                            'id' => $log->id,
                            'file' => $this->displayPath(config('filesystems.default'), $path),
                        ];
                    }
                    $bar->advance();
                }
            });

        $bar->finish();

        ksort($byType);
        foreach ($byType as $table => $counts) {
            note("  {$table}.uploads: ".number_format($counts['total']).' references, '.number_format($counts['missing']).' missing.');
        }

        return $missing;
    }

    /**
     * Resolve a polymorphic action_logs.item_type into its DB table name
     * so the orphan report attributes the file to the model that owns
     * it. Returns null for item_types the scan does not recognize.
     */
    private function tableForItemType(?string $itemType): ?string
    {
        if ($itemType === null || $itemType === '' || ! class_exists($itemType)) {
            return null;
        }

        try {
            return (new $itemType)->getTable();
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * imports.file_path stores the on-disk name of a CSV that was
     * uploaded through the importer UI. Files live in
     * private_uploads/imports/ on the default disk.
     *
     * @return array<int, array{table: string, type: string, id: int|string, file: string}>
     */
    private function scanImportFiles(int $chunk): array
    {
        $missing = [];
        $total = Import::query()
            ->whereNotNull('file_path')
            ->where('file_path', '!=', '')
            ->count();

        if ($total === 0) {
            return [];
        }

        $bar = progress(label: 'Scanning imports.file_path', steps: $total);
        $bar->start();

        Import::query()
            ->whereNotNull('file_path')
            ->where('file_path', '!=', '')
            ->chunkById($chunk, function ($rows) use (&$missing, $bar): void {
                foreach ($rows as $import) {
                    $path = FileStorage::Imports->privateStorageKey().$import->file_path;

                    if (! Storage::exists($path)) {
                        $missing[] = [
                            'table' => 'imports',
                            'type' => 'file_path',
                            'id' => $import->id,
                            'file' => $this->displayPath(config('filesystems.default'), $path),
                        ];
                    }
                    $bar->advance();
                }
            });

        $bar->finish();

        note('  imports.file_path: '.number_format($total).' references, '.number_format(count($missing)).' missing.');

        return $missing;
    }

    /**
     * checkout_acceptances holds signature PNGs under
     * FileStorage::Signatures and generated EULA PDFs under
     * FileStorage::EulaPdfs. One row can own both. The enum's
     * modelClass() returns null for these two cases because the ownership
     * is 1-to-2, so they do not fall out of the model-image loop.
     *
     * @return array<int, array{table: string, type: string, id: int|string, file: string}>
     */
    private function scanAcceptanceFiles(int $chunk): array
    {
        $missing = [];
        $total = CheckoutAcceptance::query()
            ->where(function ($q): void {
                $q->whereNotNull('signature_filename')->where('signature_filename', '!=', '')
                    ->orWhere(function ($qq): void {
                        $qq->whereNotNull('stored_eula_file')->where('stored_eula_file', '!=', '');
                    });
            })
            ->count();

        if ($total === 0) {
            return [];
        }

        $bar = progress(label: 'Scanning checkout_acceptances', steps: $total);
        $bar->start();

        $signaturesDir = FileStorage::Signatures->privateStorageKey();
        $eulaDir = FileStorage::EulaPdfs->privateStorageKey();

        CheckoutAcceptance::query()
            ->select(['id', 'signature_filename', 'stored_eula_file'])
            ->where(function ($q): void {
                $q->whereNotNull('signature_filename')->where('signature_filename', '!=', '')
                    ->orWhere(function ($qq): void {
                        $qq->whereNotNull('stored_eula_file')->where('stored_eula_file', '!=', '');
                    });
            })
            ->chunkById($chunk, function ($rows) use (&$missing, $bar, $signaturesDir, $eulaDir): void {
                foreach ($rows as $acceptance) {
                    $signature = (string) ($acceptance->signature_filename ?? '');
                    if ($signature !== '') {
                        $path = $signaturesDir.$signature;
                        if (! Storage::exists($path)) {
                            $missing[] = [
                                'table' => 'checkout_acceptances',
                                'type' => 'signature_filename',
                                'id' => $acceptance->id,
                                'file' => $this->displayPath(config('filesystems.default'), $path),
                            ];
                        }
                    }

                    $eula = (string) ($acceptance->stored_eula_file ?? '');
                    if ($eula !== '') {
                        $path = $eulaDir.$eula;
                        if (! Storage::exists($path)) {
                            $missing[] = [
                                'table' => 'checkout_acceptances',
                                'type' => 'stored_eula_file',
                                'id' => $acceptance->id,
                                'file' => $this->displayPath(config('filesystems.default'), $path),
                            ];
                        }
                    }

                    $bar->advance();
                }
            });

        $bar->finish();

        note('  checkout_acceptances: '.number_format($total).' rows checked, '.number_format(count($missing)).' missing.');

        return $missing;
    }

    /**
     * Turn a disk-relative path into something readable in the output.
     * On local disks that means prepending the disk root relative to
     * the project root, so `models/mbp9.jpg` on the public disk
     * displays as `public/uploads/models/mbp9.jpg`. On non-local
     * disks (S3, etc.) the driver's own path isn't a filesystem
     * location, so we prefix the disk name in brackets and keep the
     * relative key.
     */
    private function displayPath(string $disk, string $relative): string
    {
        $config = config("filesystems.disks.$disk");
        $driver = $config['driver'] ?? '';

        if ($driver !== 'local') {
            return "[$disk] $relative";
        }

        $root = (string) ($config['root'] ?? '');
        $base = base_path();

        if ($root !== '' && str_starts_with($root, $base)) {
            $repoRelative = ltrim(substr($root, strlen($base)), DIRECTORY_SEPARATOR.'/');

            return $repoRelative === '' ? $relative : "$repoRelative/$relative";
        }

        // Root is outside the project (unusual for local disks). Show
        // the absolute filesystem path so user can still find
        // the file.
        return rtrim($root, DIRECTORY_SEPARATOR.'/').'/'.$relative;
    }

    /**
     * @param  array<int, array{table: string, type: string, id: int|string, file: string}>  $missing
     */
    /**
     * @param  array<int, array{direction?: string, table: string, type: string, id: int|string, file: string}>  $rows
     */
    private function renderReport(array $rows, bool $summary): void
    {
        if ($rows === []) {
            info('No mismatches found. DB and disk are in sync.');

            return;
        }

        $tallies = $this->tallyByReference($rows);
        $idGroups = $this->idsByReference($rows);
        $totals = [];
        foreach ($tallies as $key => $count) {
            [$direction, $table, $type] = explode('.', $key, 3);
            $totals[] = [$direction, $table, $type, number_format($count), $this->formatIdList($idGroups[$key] ?? [])];
        }
        $totals[] = ['', 'TOTAL', '', number_format(count($rows)), ''];

        table(['Direction', 'Table', 'Type', 'Count', 'Row IDs'], $totals);

        if ($summary) {
            return;
        }

        warning('Full list of mismatches:');
        table(
            ['Direction', 'Table', 'ID', 'Type', 'File'],
            array_map(fn (array $row): array => [
                $row['direction'] ?? 'missing',
                $row['table'],
                (string) $row['id'],
                $row['type'],
                $row['file'],
            ], $rows),
        );
    }

    /**
     * Roll rows up by direction.table.type so the summary table can
     * show counts per direction / column source.
     *
     * @param  array<int, array{direction?: string, table: string, type: string, id: int|string, file: string}>  $rows
     * @return array<string, int>
     */
    private function tallyByReference(array $rows): array
    {
        $tally = [];
        foreach ($rows as $row) {
            $key = ($row['direction'] ?? 'missing').'.'.$row['table'].'.'.$row['type'];
            $tally[$key] = ($tally[$key] ?? 0) + 1;
        }
        ksort($tally);

        return $tally;
    }

    /**
     * Group IDs by direction.table.type so the summary table can show
     * which rows to go look at without having to scroll through the
     * full list. Orphan rows all carry id '-' and still bucket together
     * so the summary stays readable.
     *
     * @param  array<int, array{direction?: string, table: string, type: string, id: int|string, file: string}>  $rows
     * @return array<string, array<int, int|string>>
     */
    private function idsByReference(array $rows): array
    {
        $ids = [];
        foreach ($rows as $row) {
            $key = ($row['direction'] ?? 'missing').'.'.$row['table'].'.'.$row['type'];
            $ids[$key][] = $row['id'];
        }

        return $ids;
    }

    /**
     * Cap the ID list at ~10 to keep the table row from wrapping to
     * three lines on installs with hundreds of missing files. The
     * full list is still available in the per-row table below (or
     * via --csv / --json).
     *
     * @param  array<int, int|string>  $ids
     */
    private function formatIdList(array $ids): string
    {
        if ($ids === []) {
            return '';
        }

        // Orphan rows carry id '-' because there's no DB row behind
        // them, so a group of all-dashes conveys nothing. Suppress to
        // keep the summary readable.
        $meaningful = array_values(array_filter($ids, fn ($id) => $id !== '-'));
        if ($meaningful === []) {
            return '';
        }

        $limit = 10;
        if (count($meaningful) <= $limit) {
            return implode(', ', $meaningful);
        }

        $head = array_slice($meaningful, 0, $limit);

        return implode(', ', $head).', ... ('.number_format(count($meaningful) - $limit).' more)';
    }

    /**
     * @param  array<int, array{direction?: string, table: string, type: string, id: int|string, file: string}>  $rows
     */
    private function writeCsv(string $csvPath, array $rows): void
    {
        $handle = fopen($csvPath, 'w');
        if ($handle === false) {
            warning("Could not open CSV path for writing: $csvPath");

            return;
        }

        fputcsv($handle, ['direction', 'table', 'id', 'type', 'file'], escape: '\\', eol: "\r\n");
        foreach ($rows as $row) {
            fputcsv($handle, [
                $row['direction'] ?? 'missing',
                $row['table'],
                $row['id'],
                $row['type'],
                $row['file'],
            ], escape: '\\', eol: "\r\n");
        }
        fclose($handle);

        note('Wrote '.number_format(count($rows))." row(s) to $csvPath.");
    }

    /**
     * Resolve the list of scan categories. Explicit --only flag wins so
     * scripted callers stay deterministic. Otherwise, in an interactive
     * tty with no --json, prompt the admin with a multiselect so they
     * can narrow the sweep without memorizing the flag format. JSON
     * mode and non-tty invocations both fall through to the full set.
     *
     * @return array<int, string>
     */
    private function parseOnlyOption(bool $json): array
    {
        $valid = ['images', 'settings', 'action_logs', 'imports', 'acceptances'];
        $only = (string) $this->option('only');

        if ($only === '') {
            if (! $json && $this->input->isInteractive()) {
                $selected = multiselect(
                    label: 'Which categories should the sweep check?',
                    options: $valid,
                    default: $valid,
                    required: 'Pick at least one category.',
                );

                return array_values($selected);
            }

            return $valid;
        }

        $requested = array_values(array_filter(array_map('trim', explode(',', $only))));
        $intersect = array_values(array_intersect($valid, $requested));

        if ($intersect === []) {
            warning('No valid categories in --only. Valid: '.implode(', ', $valid).'. Falling back to all.');

            return $valid;
        }

        return $intersect;
    }
}
