<?php

namespace Tests\Feature;

use App\Livewire\Partials\Advancedsearch\Modal;
use App\Livewire\Partials\Advancedsearch\AdvancedsearchModalAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Mockery;
use Tests\TestCase;
use App\Services\PredefinedFilterService;
use App\Models\User;

class PredefinedFilterModalTest extends TestCase
{
    use RefreshDatabase;

    protected function makeServiceMock(array $overrides=[])
    {
        $mock = Mockery::mock(PredefinedFilterService::class);

        // Default permissive stubs
        $mock->shouldIgnoreMissing();

        foreach ($overrides as $method => $return) {
            $mock->shouldReceive($method)->andReturn($return);
        }

        $this->app->instance(PredefinedFilterService::class, $mock);

        return $mock;
    }

    protected function loginUser()
    {
        $user = User::factory()->create();

        $this->be($user);

        return $user;
    }

    public function testOpenModalForCreationWithAllParameters()
    {
        $this->loginUser();
        $this->makeServiceMock();

        $filterData = ['foo' => 'bar'];

        Livewire::test(Modal::class)
            ->dispatch('openPredefinedFiltersModal',
                app(PredefinedFilterService::class),
                'create',
                $filterData,
                999 // even though not used for create, we pass it to ensure it is set
            )
            ->assertSet('showModal', true)
            ->assertSet('modalActionType', AdvancedsearchModalAction::Create)
            ->assertSet('filterData', $filterData)
            ->assertSet('filterId', 999) // Because we passed it; depending on desired behavior you may assert null instead
            ->assertSee('Name')        // Adjust to actual label or translation output
            ->assertSee('Visibility')
            ->assertSee('Select a Group')
            ->assertSee('Save')
            ->assertSee('Close');
            
    }

    public function testOpenModalForCreationWithOnlyRequiredParameters()
    {
        $this->loginUser();
        $this->makeServiceMock();

        Livewire::test(Modal::class)
            ->dispatch('openPredefinedFiltersModal',
                app(PredefinedFilterService::class),
                'create',      // required
                null,          // optional filter data
                null           // optional id
            )
            ->assertSet('showModal', true)
            ->assertSet('modalActionType', AdvancedsearchModalAction::Create)
            ->assertSet('filterData', null)
            ->assertSet('filterId', null)
            ->assertSee('Name')
            ->assertSee('Visibility')
            ->assertSee('Select a Group')
            ->assertSee('Save')
            ->assertSee('Close');
    }


    public function testOpenModalForEditWithOnlyRequiredParameters()
    {
        $this->loginUser();

        $service = $this->makeServiceMock([
            'getFilterById' => [
                'name' => 'Existing Filter',
                'is_public' => 0,
                'permissions' => collect([]),
            ],
        ]);

        // Passing null filter data and null ID puts component into Edit action but without ID: no look-up branch executed.
        Livewire::test(Modal::class)
            ->dispatch('openPredefinedFiltersModal',
                $service,
                'edit',
                null,
                null
            )
            ->assertSet('modalActionType', AdvancedsearchModalAction::Edit)
            ->assertSet('filterId', null)
            ->assertSet('name', '') // Not populated since ID was null
            ->assertSee('Name')
            ->assertSee('Visibility')
            ->assertSee('Select a Group')
            ->assertSee('edit')
            ->assertSee('Close');
    }


    public function testOpenModalForDeletionWithOnlyRequiredParameters()
    {
        $this->loginUser();
        $this->makeServiceMock();

        Livewire::test(Modal::class)
            ->dispatch('openPredefinedFiltersModal',
                app(PredefinedFilterService::class),
                'delete',
                null,
                456
            )
            ->assertSet('showModal', true)
            ->assertSet('modalActionType', AdvancedsearchModalAction::Delete)
            ->assertSet('filterId', 456)
            ->assertSee('Delete')
            ->assertSee('Close');
    }

    public function testOpenModalForDeletionWithMissingParameters()
    {
        $this->loginUser();
        $this->makeServiceMock();

        Livewire::test(Modal::class)
            ->dispatch('openPredefinedFiltersModal',
                app(PredefinedFilterService::class),
                'delete',
                null,
                null
            )
            ->assertSet('modalActionType', AdvancedsearchModalAction::Delete)
            ->assertSet('filterId', null)
            ->assertSee('Delete')
            ->assertSee('Close');
    }
}
