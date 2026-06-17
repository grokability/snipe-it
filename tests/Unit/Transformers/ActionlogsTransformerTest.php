<?php

namespace Tests\Unit\Transformers;

use App\Http\Transformers\ActionlogsTransformer;
use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\Setting;
use App\Models\User;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ActionlogsTransformerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->superuser()->create());
    }

    private function logWithAction(string $actionType): Actionlog
    {
        $asset = Asset::factory()->create();
        $target = User::factory()->create();

        return Actionlog::factory()->create([
            'action_type' => $actionType,
            'item_id' => $asset->id,
            'item_type' => Asset::class,
            'target_id' => $target->id,
            'target_type' => User::class,
        ]);
    }

    public static function actionTypeProvider(): array
    {
        return [
            ['checkout'], ['checkin from'], ['update'], ['create'],
            ['delete'], ['audit'], ['accepted'], ['declined'], ['uploaded'],
        ];
    }

    #[DataProvider('actionTypeProvider')]
    public function test_transforms_actionlog_for_each_action_type(string $actionType): void
    {
        $log = $this->logWithAction($actionType);

        $result = (new ActionlogsTransformer)->transformActionlog($log, Setting::getSettings());

        $this->assertIsArray($result);
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('action_type', $result);
    }

    public function test_transforms_collection_of_actionlogs(): void
    {
        $this->logWithAction('checkout');
        $this->logWithAction('update');
        $logs = Actionlog::query()->get();

        $result = (new ActionlogsTransformer)->transformActionlogs($logs, $logs->count());

        $this->assertArrayHasKey('rows', $result);
        $this->assertEquals($logs->count(), $result['total']);
        $this->assertNotEmpty($result['rows']);
    }
}
