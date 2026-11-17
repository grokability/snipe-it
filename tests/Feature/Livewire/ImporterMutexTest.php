<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Importer;
use App\Models\Import;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Regression coverage for the per-file import mutex. Two admins (or
 * two tabs) attempting to process the SAME import file concurrently
 * would double-insert rows and race the slice index that drives the
 * per-slice API POSTs. Different files stay independent because the
 * key is scoped by file id.
 */
class ImporterMutexTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->flushImportLocks();
    }

    protected function tearDown(): void
    {
        $this->flushImportLocks();
        parent::tearDown();
    }

    private function flushImportLocks(): void
    {
        foreach (Import::query()->pluck('id') as $id) {
            Cache::store('file')->forget('import:'.$id.':running');
        }
    }

    public function test_start_processing_is_rejected_when_the_files_lock_is_held(): void
    {
        $this->actingAs(User::factory()->superuser()->create());
        $file = Import::factory()->create();

        Cache::store('file')->put($file->lockKey(), true, now()->addMinutes(30));

        $component = Livewire::test(Importer::class)
            ->set('activeFileId', $file->id)
            ->call('startProcessing');

        $component->assertSet('processing', false);
        $component->assertSet('message_type', 'danger');
        $component->assertSet('message', trans('general.operation_already_running', [
            'operation' => trans('general.import'),
        ]));
    }

    public function test_locks_are_scoped_per_file_so_a_second_file_can_still_start(): void
    {
        $this->actingAs(User::factory()->superuser()->create());
        [$fileA, $fileB] = Import::factory()->count(2)->create();

        Cache::store('file')->put($fileA->lockKey(), true, now()->addMinutes(30));

        $component = Livewire::test(Importer::class)
            ->set('activeFileId', $fileB->id)
            ->call('startProcessing');

        $component->assertSet('processing', true);
        $this->assertTrue(Cache::store('file')->has($fileA->lockKey()));
        $this->assertTrue(Cache::store('file')->has($fileB->lockKey()));
    }

    public function test_stop_processing_releases_the_lock(): void
    {
        $this->actingAs(User::factory()->superuser()->create());
        $file = Import::factory()->create();

        $component = Livewire::test(Importer::class)
            ->set('activeFileId', $file->id)
            ->call('startProcessing');

        $this->assertTrue(Cache::store('file')->has($file->lockKey()));

        $component->call('stopProcessing');

        $this->assertFalse(Cache::store('file')->has($file->lockKey()));
    }
}
