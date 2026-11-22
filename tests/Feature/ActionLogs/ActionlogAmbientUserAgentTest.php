<?php

namespace Tests\Feature\ActionLogs;

use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Coverage for `Actionlog::withActionSource(..., userAgent: ...)`.
 * The user_agent lets sync-adapter pulls and the MCP server
 * stamp observer-driven writes with the owning operation's name,
 * instead of the triggering HTTP client's `User-Agent` header (which
 * would record the admin's browser for a "Sync Now" click, or the
 * MCP client's UA for a tool call).
 */
class ActionlogAmbientUserAgentTest extends TestCase
{
    #[Test]
    public function ambient_user_agent_wins_over_request_header(): void
    {
        // Pretend the triggering HTTP request is from a browser.
        request()->headers->set('User-Agent', 'Mozilla/5.0 (should-not-appear)');

        $actor = User::factory()->create();
        $this->actingAs($actor);

        $asset = null;
        Actionlog::withActionSource(
            'sync:test',
            function () use (&$asset) {
                $asset = Asset::factory()->create();
            },
            userAgent: 'Snipe-IT sync: test',
        );

        $log = Actionlog::query()
            ->where('item_type', Asset::class)
            ->where('item_id', $asset->id)
            ->latest('id')
            ->first();

        $this->assertNotNull($log);
        $this->assertSame('Snipe-IT sync: test', $log->user_agent);
        $this->assertSame('sync:test', $log->action_source);
    }

    #[Test]
    public function missing_user_agent_argument_leaves_request_header_in_place(): void
    {
        // Back-compat for every pre-existing withActionSource caller
        // (there were already a handful shipping without the second
        // argument when the parameter was added). A null $userAgent
        // must not clobber the ambient slot.
        request()->headers->set('User-Agent', 'curl/8.0');

        $actor = User::factory()->create();
        $this->actingAs($actor);

        $asset = null;
        Actionlog::withActionSource('ldap', function () use (&$asset) {
            $asset = Asset::factory()->create();
        });

        $log = Actionlog::query()
            ->where('item_type', Asset::class)
            ->where('item_id', $asset->id)
            ->latest('id')
            ->first();

        $this->assertNotNull($log);
        $this->assertSame('curl/8.0', $log->user_agent);
    }

    #[Test]
    public function ambient_user_agent_is_restored_after_the_scope_exits(): void
    {
        request()->headers->set('User-Agent', 'Mozilla/5.0 (post-scope)');

        $actor = User::factory()->create();
        $this->actingAs($actor);

        Actionlog::withActionSource(
            'sync:test',
            fn () => null,
            userAgent: 'Snipe-IT sync: test',
        );

        // After the scope exits, a subsequent log must go back to
        // reading the request header rather than carrying the
        // previous ambient value.
        $asset = Asset::factory()->create();

        $log = Actionlog::query()
            ->where('item_type', Asset::class)
            ->where('item_id', $asset->id)
            ->latest('id')
            ->first();

        $this->assertSame('Mozilla/5.0 (post-scope)', $log->user_agent);
    }

    #[Test]
    public function nested_scopes_restore_the_outer_user_agent(): void
    {
        request()->headers->set('User-Agent', 'outer-request-ua');

        $actor = User::factory()->create();
        $this->actingAs($actor);

        $outerAsset = null;
        $innerAsset = null;
        $afterInnerAsset = null;

        Actionlog::withActionSource('outer', function () use (&$outerAsset, &$innerAsset, &$afterInnerAsset) {
            $outerAsset = Asset::factory()->create();

            Actionlog::withActionSource(
                'inner',
                function () use (&$innerAsset) {
                    $innerAsset = Asset::factory()->create();
                },
                userAgent: 'inner-ua',
            );

            // Back inside the outer scope: outer never set a user_agent,
            // so the request header wins again (the inner value must
            // not leak out).
            $afterInnerAsset = Asset::factory()->create();
        }, userAgent: 'outer-ua');

        $outerLog = Actionlog::where('item_id', $outerAsset->id)->latest('id')->first();
        $innerLog = Actionlog::where('item_id', $innerAsset->id)->latest('id')->first();
        $afterInnerLog = Actionlog::where('item_id', $afterInnerAsset->id)->latest('id')->first();

        $this->assertSame('outer-ua', $outerLog->user_agent);
        $this->assertSame('inner-ua', $innerLog->user_agent);
        $this->assertSame('outer-ua', $afterInnerLog->user_agent);
    }
}
