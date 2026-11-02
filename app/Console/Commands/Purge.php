<?php

namespace App\Console\Commands;

use App\Enums\ActionType;
use App\Enums\FileStorage;
use App\Models\Accessory;
use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Category;
use App\Models\CheckoutAcceptance;
use App\Models\Company;
use App\Models\Component;
use App\Models\Consumable;
use App\Models\Department;
use App\Models\License;
use App\Models\Location;
use App\Models\Manufacturer;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use ReflectionClass;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\warning;

class Purge extends Command
{
    /**
     * The `--force=true` back-compat: the settings UI at
     * SettingsController::postPurge() calls this with
     * `['--force' => 'true', '--no-interaction' => true]`.
     *
     * @var string
     */
    protected $signature = 'snipeit:purge
        {--force=false : Skip the confirmation prompt (accepts "true").}
        {--dry-run : Report what would be purged without deleting anything.}';

    protected $description = 'Purge all soft-deleted records in the database. Walks every model that uses the SoftDeletes trait, DELETEs the trashed rows, cleans up their polymorphic action_log children, and removes their uploaded files and image assets from disk. No undo.';

    public function handle(): int
    {
        $force = $this->option('force') === 'true';
        $dryRun = (bool) $this->option('dry-run');

        if (! $force) {
            warning('This will PERMANENTLY delete every soft-deleted record in the database. There is no undo.');

            if (! confirm('Continue with the purge?', default: false)) {
                $this->info('Cancelled. Nothing was purged.');

                return self::SUCCESS;
            }
        }

        $started = microtime(true);
        $summary = [];

        foreach ($this->discoverSoftDeletableModels() as $modelClass) {
            foreach ($this->purgeModel($modelClass, $dryRun) as $row) {
                $summary[] = $row;
            }
        }

        $elapsed = microtime(true) - $started;

        $this->newLine();

        if ($dryRun) {
            $this->components->info('Dry run complete. No records were deleted.');
        } else {
            $this->components->info('Purge complete.');
        }

        if (empty($summary)) {
            $this->info('Nothing to purge.');
        } else {
            $this->table(['Resource', $dryRun ? 'Would purge' : 'Purged'], $summary);
        }

        $this->info(sprintf('Elapsed: %.3fs   Peak memory: %s MB',
            $elapsed,
            round(memory_get_peak_usage(true) / 1024 / 1024, 2)
        ));

        return self::SUCCESS;
    }

    /**
     * Walk app/Models/ and return the FQCNs of every concrete Eloquent
     * model that uses the SoftDeletes trait. Runtime discovery means a new
     * soft-deletable model gets picked up automatically without touching
     * this command.
     *
     * Dedupe by table so single-table inheritance / subclassed models
     * (e.g. SCIMUser extends User, same `users` table) don't get processed
     * twice. The first pass would run without the subclass-specific
     * filters (`show_in_list != 0` for users) and delete the rows the
     * parent's filter was supposed to preserve. Prefer the base class:
     * the more-derived class is skipped if a parent for its table was
     * already added.
     *
     * @return list<class-string<Model>>
     */
    private function discoverSoftDeletableModels(): array
    {
        $candidates = [];
        foreach (glob(app_path('Models/*.php')) as $file) {
            $class = 'App\\Models\\'.basename($file, '.php');
            if (! class_exists($class)) {
                continue;
            }
            $ref = new ReflectionClass($class);
            if ($ref->isAbstract() || ! $ref->isSubclassOf(Model::class)) {
                continue;
            }
            if (! in_array(SoftDeletes::class, class_uses_recursive($class), true)) {
                continue;
            }
            $candidates[] = $class;
        }

        // Dedupe by table, keeping the base-most class in each group so
        // filters on the parent class apply. Sort by inheritance depth
        // ascending, then walk and keep the first per table.
        usort($candidates, fn ($a, $b) => count(class_parents($a)) <=> count(class_parents($b)));

        $seen = [];
        $result = [];
        foreach ($candidates as $class) {
            $table = (new $class)->getTable();
            if (isset($seen[$table])) {
                continue;
            }
            $seen[$table] = true;
            $result[] = $class;
        }

        return $result;
    }

    /**
     * Nuke one model's trashed rows: pluck the trashed ids, delete
     * on-disk files (images and uploaded files) associated with those
     * rows, wipe polymorphic action_log children, wipe FK child tables,
     * then bulk-delete the parents by their `deleted_at` index.
     *
     * @return array<int, array{0: string, 1: int}>
     */
    private function purgeModel(string $modelClass, bool $dryRun): array
    {
        $model = new $modelClass;
        $table = $model->getTable();
        $label = class_basename($modelClass);

        $parentQuery = DB::table($table)->whereNotNull('deleted_at');

        // show_in_list=0 excludes a user from checkout-target dropdowns
        // in the UI. Preserved by the purge (matches the pre-refactor
        // behavior) so users with this flag stick around even when
        // soft-deleted.
        if ($modelClass === User::class) {
            $parentQuery->where('show_in_list', '!=', '0');
        }

        $ids = (clone $parentQuery)->pluck('id');
        if ($ids->isEmpty()) {
            return [];
        }

        // File cleanup runs before the DB deletes so we can still read
        // the image/avatar column off the parent row and correlate
        // action_logs to a still-existing parent. Skipped during dry-run
        // so `--dry-run` truly writes nothing.
        if (! $dryRun) {
            $this->deleteImageFiles($modelClass, $table, $ids);
            $this->deleteActionLogFiles($modelClass, $ids);
            $this->deletePrivateFileColumns($modelClass, $table, $ids);
        }

        // Polymorphic action_log cleanup. Every model referenced by
        // action_logs uses one of two column pairs. Users use target_*,
        // everything else uses item_*. Chunked whereIn turns what used
        // to be N*2 individual DELETEs into (N/1000)*2 bulk ones. Fixes
        // the big-purge timeout + memory bomb on sites with hundreds
        // of thousands of trashed rows.
        $itemLogs = 0;
        $targetLogs = 0;
        foreach ($ids->chunk(1000) as $chunk) {
            $itemQuery = DB::table('action_logs')
                ->where('item_type', $modelClass)
                ->whereIn('item_id', $chunk);
            $targetQuery = DB::table('action_logs')
                ->where('target_type', $modelClass)
                ->whereIn('target_id', $chunk);
            if ($dryRun) {
                $itemLogs += $itemQuery->count();
                $targetLogs += $targetQuery->count();
            } else {
                $itemLogs += $itemQuery->delete();
                $targetLogs += $targetQuery->delete();
            }
        }

        // Child-table cleanup: rows in other tables that belong to a
        // trashed parent by a plain foreign key. Nuked whole rather than
        // only-trashed because a live LicenseSeat pointing at a purged
        // License is an orphan by definition. Auto-discovery finds all
        // soft-deletable models, but a trashed License with live
        // LicenseSeats or a trashed Asset with live Maintenances would
        // leave orphans behind if we only nuked soft-deleted rows.
        // Two shapes: a plain FK column name (`license_seats.license_id`)
        // or a `[column, type_column, expected_type]` triple for
        // polymorphic child tables (`maintenances.item_id` paired with
        // `item_type='App\Models\Asset'`). The polymorphic form is needed
        // now that maintenances live on `item_id`/`item_type` and could
        // point at non-Asset parents; without the type guard, purging a
        // trashed Asset would also delete accessory-owned maintenances
        // that happen to share the same numeric id.
        $childTables = [
            Asset::class => [
                'maintenances' => ['item_id', 'item_type', Asset::class],
                'checkout_requests' => ['requestable_id', 'requestable_type', Asset::class],
                // Sync-adapter identity rows tying this asset to a
                // vendor host. Orphaned once the asset is purged, and
                // an orphan would cause the next sync to skip creating
                // a fresh asset for that vendor host because
                // provisionAsset() finds the dead link first.
                'asset_external_sources' => 'asset_id',
            ],
            License::class => [
                'license_seats' => 'license_id',
                'checkout_requests' => ['requestable_id', 'requestable_type', License::class],
            ],
            // Every model carrying the Requestable trait needs its
            // pending / historical CheckoutRequest rows nuked when
            // the parent is purged, or those rows linger as orphans
            // in the admin queue and the requester's own /account/
            // requested page. Cascade uses the polymorphic
            // requestable_id + requestable_type pair; the trait's
            // forceDeleted hook covers the direct forceDelete() path
            // that bypasses this command.
            Accessory::class => ['checkout_requests' => ['requestable_id', 'requestable_type', Accessory::class]],
            // consumables_users is the pivot the Consumable observer
            // no longer detaches on soft-delete (so restores preserve
            // the mark-consumed lifecycle). On purge we need to clean
            // it ourselves since this path bypasses the forceDeleted
            // Eloquent hook that handles pivot detach for in-app
            // force-delete.
            Consumable::class => [
                'consumables_users' => 'consumable_id',
                'checkout_requests' => ['requestable_id', 'requestable_type', Consumable::class],
            ],
            Component::class => ['checkout_requests' => ['requestable_id', 'requestable_type', Component::class]],
            AssetModel::class => ['checkout_requests' => ['requestable_id', 'requestable_type', AssetModel::class]],
        ];
        // Chunked whereIn, same pattern as the action_log cleanup
        // above. Replaces N*k individual DELETEs (k = number of
        // child-table entries for this model) with (N/1000)*k bulk ones.
        $childCounts = [];
        if (array_key_exists($modelClass, $childTables)) {
            foreach ($childTables[$modelClass] as $childTable => $foreignKey) {
                $count = 0;
                foreach ($ids->chunk(1000) as $chunk) {
                    $q = DB::table($childTable);
                    if (is_array($foreignKey)) {
                        [$idColumn, $typeColumn, $expectedType] = $foreignKey;
                        $q->whereIn($idColumn, $chunk)->where($typeColumn, $expectedType);
                    } else {
                        $q->whereIn($foreignKey, $chunk);
                    }
                    $count += $dryRun ? $q->count() : $q->delete();
                }
                if ($count > 0) {
                    $childCounts[$childTable] = $count;
                }
            }
        }

        $parentCount = $dryRun ? $ids->count() : $parentQuery->delete();

        $rows = [];
        $rows[] = [$label, $parentCount];
        if ($itemLogs > 0) {
            $rows[] = [$label.' action_logs (item)', $itemLogs];
        }
        if ($targetLogs > 0) {
            $rows[] = [$label.' action_logs (target)', $targetLogs];
        }
        foreach ($childCounts as $childTable => $count) {
            $rows[] = [$childTable, $count];
        }

        return $rows;
    }

    /**
     * Delete image/avatar files stored on the public disk for the
     * trashed rows. Reads the filename off each trashed parent row,
     * then unlinks `{subpath}/{filename}` from the public disk.
     *
     * Only lives here in the purge (not in each controller's destroy
     * method) so that soft-deleting a row does NOT delete the image.
     * That way a soft-deleted row can be restored with its image intact.
     * The image is only permanently removed when the row is permanently
     * removed (via this purge).
     */
    private function deleteImageFiles(string $modelClass, string $table, Collection $ids): void
    {
        // Image/avatar files stored on the public disk, keyed by parent
        // model. Value is `column_name => FileStorage case`. Purge
        // reads the filename off each trashed parent row and unlinks
        // `{case->publicPath()}{column_value}` from the public disk.
        $imageFiles = [
            User::class => ['avatar' => FileStorage::Avatars],
            Asset::class => ['image' => FileStorage::Assets],
            AssetModel::class => ['image' => FileStorage::Models],
            Accessory::class => ['image' => FileStorage::Accessories],
            Category::class => ['image' => FileStorage::Categories],
            Company::class => ['image' => FileStorage::Companies],
            Component::class => ['image' => FileStorage::Components],
            Consumable::class => ['image' => FileStorage::Consumables],
            Department::class => ['image' => FileStorage::Departments],
            Location::class => ['image' => FileStorage::Locations],
            Manufacturer::class => ['image' => FileStorage::Manufacturers],
            Supplier::class => ['image' => FileStorage::Suppliers],
        ];

        if (! array_key_exists($modelClass, $imageFiles)) {
            return;
        }

        foreach ($imageFiles[$modelClass] as $column => $case) {
            // Chunk the IN list so a user with tens of thousands of
            // trashed rows doesn't blow MySQL's placeholder ceiling
            // on a single prepared statement.
            $filenames = $ids->chunk(1000)->flatMap(fn ($chunk) => DB::table($table)
                ->whereIn('id', $chunk)
                ->pluck($column)
            )->filter()->unique();

            foreach ($filenames as $filename) {
                try {
                    // User avatar values can be absolute OAuth URLs
                    // (Google / Microsoft / Gravatar) that do not point at
                    // anything on this disk. basename below would reduce
                    // such a URL to its tail segment and attempt to delete
                    // avatars/{tail} which could collide with a legitimate
                    // other-user avatar.
                    if ($modelClass === User::class
                        && preg_match('#^https?://#i', (string) $filename) === 1) {
                        continue;
                    }
                    $key = $case->publicPath().basename($filename);
                    if (Storage::disk('public')->exists($key)) {
                        Storage::disk('public')->delete($key);
                    }
                } catch (\Exception $e) {
                    Log::info(sprintf(
                        'snipeit:purge - error deleting %s file %s for %s: %s',
                        $column, $filename, $modelClass, $e->getMessage()
                    ));
                }
            }
        }
    }

    /**
     * Delete every file referenced by action_logs whose parent row is
     * about to be purged. Covers four categories of file, keyed by
     * `action_type` on the log:
     *
     *   - `uploaded`  → `private_uploads/{type}/` (Files tab attachments)
     *   - `audit`     → `private_uploads/audits/`
     *   - `accepted`  → `private_uploads/eula-pdfs/`
     *   - `declined`  → `private_uploads/eula-pdfs/`
     *
     * Plus, independent of action_type, the `accept_signature` column
     * can point at a signature file under `private_uploads/signatures/`.
     *
     * Match rows via BOTH the item_* and target_* column pairs. When
     * purging a user, we want signatures/EULAs stored under target_id
     * (the accepting user) even though the checkoutable item's
     * item_type points at Asset/License/etc.
     *
     * Failure to unlink is logged but not fatal.
     */
    private function deleteActionLogFiles(string $modelClass, Collection $ids): void
    {
        // "Files" tab attachment roots under private_uploads/, keyed by
        // the parent model of the file. These are the contracts, receipts,
        // photos, etc. tracked in action_logs with action_type = 'uploaded'.
        // Not done at soft-delete time so restoring a soft-deleted row
        // brings the files back with it. Built by iterating FileStorage
        // so adding a new case there wires it up here automatically.
        // Only private-scope cases that resolve to a single Eloquent
        // model show up (Audits / Signatures / EulaPdfs / Imports /
        // Backups return null from modelClass() and are skipped).
        $uploadRoots = [];
        foreach (FileStorage::cases() as $case) {
            if ($case->hasPrivateScope() && ($model = $case->modelClass()) !== null) {
                $uploadRoots[$model] = $case->privateStorageKey();
            }
        }

        // Chunk the parent ids AND stream the matching action_logs via
        // chunkById so only one page of logs lives in memory at a time.
        // The old shape collected every log row into a single Collection
        // (via flatMap(...)->get()) and OOM'd a web request on sites
        // with hundreds of thousands of rows per model. Each chunk's
        // paths unlink immediately before the next chunk loads.
        //
        // $seenPaths dedupes across chunks so the same signature or
        // EULA file (referenced by multiple logs) isn't unlinked twice.
        $seenPaths = [];
        foreach ($ids->chunk(1000) as $idChunk) {
            DB::table('action_logs')
                ->select('id', 'action_type', 'item_type', 'filename', 'accept_signature')
                ->where(function ($outer) use ($modelClass, $idChunk) {
                    $outer->where(function ($s) use ($modelClass, $idChunk) {
                        $s->where('item_type', $modelClass)->whereIn('item_id', $idChunk);
                    })->orWhere(function ($s) use ($modelClass, $idChunk) {
                        $s->where('target_type', $modelClass)->whereIn('target_id', $idChunk);
                    });
                })
                ->orderBy('id')
                ->chunkById(1000, function ($logs) use (&$seenPaths, $uploadRoots) {
                    foreach ($logs as $log) {
                        // Map action_type to disk path for the attached file.
                        // Mirrors Actionlog::uploads_file_path() but works
                        // off raw query-builder rows (no Eloquent).
                        if (! empty($log->filename)) {
                            if ($log->action_type === ActionType::Accepted->value || $log->action_type === ActionType::Declined->value) {
                                $path = FileStorage::EulaPdfs->privateStorageKey().$log->filename;
                            } elseif ($log->action_type === ActionType::Audit->value) {
                                $path = FileStorage::Audits->privateStorageKey().$log->filename;
                            } elseif ($log->item_type && isset($uploadRoots[$log->item_type])) {
                                $path = $uploadRoots[$log->item_type].$log->filename;
                            } else {
                                $path = null;
                            }
                            if ($path !== null && ! isset($seenPaths[$path])) {
                                $seenPaths[$path] = true;
                                $this->tryUnlink($path);
                            }
                        }
                        if (! empty($log->accept_signature)) {
                            $sigPath = FileStorage::Signatures->privateStorageKey().$log->accept_signature;
                            if (! isset($seenPaths[$sigPath])) {
                                $seenPaths[$sigPath] = true;
                                $this->tryUnlink($sigPath);
                            }
                        }
                    }
                });
        }
    }

    /**
     * Delete files referenced by columns on the parent row itself
     * (as opposed to action_logs). Covers CheckoutAcceptance's
     * `signature_filename` and `stored_eula_file`, which store their
     * paths inline on the row rather than in a related action_log.
     */
    private function deletePrivateFileColumns(string $modelClass, string $table, Collection $ids): void
    {
        $privateFileColumns = [
            CheckoutAcceptance::class => [
                'signature_filename' => FileStorage::Signatures->privateStorageKey(),
                'stored_eula_file' => FileStorage::EulaPdfs->privateStorageKey(),
            ],
        ];

        if (! array_key_exists($modelClass, $privateFileColumns)) {
            return;
        }

        foreach ($privateFileColumns[$modelClass] as $column => $subpath) {
            // Chunk the IN list
            $filenames = $ids->chunk(1000)->flatMap(fn ($chunk) => DB::table($table)
                ->whereIn('id', $chunk)
                ->pluck($column)
            )->filter()->unique();

            foreach ($filenames as $filename) {
                $this->tryUnlink(rtrim($subpath, '/').'/'.basename($filename));
            }
        }
    }

    /**
     * Storage::delete with a log-and-continue on failure. All private-
     * disk unlink calls funnel through here so error handling stays
     * uniform.
     */
    private function tryUnlink(string $key): void
    {
        try {
            if (Storage::exists($key)) {
                Storage::delete($key);
            }
        } catch (\Exception $e) {
            Log::info('snipeit:purge - error deleting '.$key.': '.$e->getMessage());
        }
    }
}
