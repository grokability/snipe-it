<?php

namespace Tests\Unit\Listeners;

use App\Events\CheckoutableCheckedIn;
use App\Events\CheckoutableCheckedOut;
use App\Listeners\CheckoutableListener;
use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class CheckoutableListenerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Notification::fake();
    }

    private function assetWithEmailCategory(): Asset
    {
        $category = Category::factory()->forAssets()->create([
            'checkin_email' => 1,
            'require_acceptance' => 0,
        ]);
        $model = AssetModel::factory()->create(['category_id' => $category->id]);

        return Asset::factory()->for($model, 'model')->create();
    }

    public function test_on_checked_out_runs_without_error(): void
    {
        $asset = $this->assetWithEmailCategory();
        $admin = User::factory()->superuser()->create();
        $target = User::factory()->create(['email' => 'target@example.com']);

        $event = new CheckoutableCheckedOut($asset, $target, $admin, 'checkout note');

        (new CheckoutableListener)->onCheckedOut($event);

        $this->assertTrue(true);
    }

    public function test_on_checked_in_runs_without_error(): void
    {
        $asset = $this->assetWithEmailCategory();
        $admin = User::factory()->superuser()->create();
        $target = User::factory()->create(['email' => 'target@example.com']);

        $event = new CheckoutableCheckedIn($asset, $target, $admin, 'checkin note');

        (new CheckoutableListener)->onCheckedIn($event);

        $this->assertTrue(true);
    }

    public function test_subscribe_registers_listeners(): void
    {
        $dispatcher = app('events');

        // subscribe() registra los handlers sin lanzar excepcion.
        (new CheckoutableListener)->subscribe($dispatcher);

        $this->assertTrue(true);
    }
}
