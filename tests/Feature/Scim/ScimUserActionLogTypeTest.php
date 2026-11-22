<?php

namespace Tests\Feature\Scim;

use App\Models\Actionlog;
use App\Models\SCIMUser;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\Passport;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Coverage for issue #20118: `SCIMUser` was being written into
 * `action_logs.item_type` / `target_type` because the Loggable
 * trait reads `$this::class` at write time, and `SCIMUser` inherits
 * Loggable through `User`. The frontend activity-log link formatter
 * only knows `user` and renders `//<id>` instead of `/users/<id>`
 * when it sees anything else. Fix normalizes the write path to the
 * canonical `User` class and ships a migration to backfill existing
 * rows.
 */
class ScimUserActionLogTypeTest extends TestCase
{
    #[Test]
    public function creating_a_scim_user_logs_item_type_as_user_not_scim_user(): void
    {
        Passport::actingAs(User::factory()->superuser()->create());

        $scimUser = new SCIMUser;
        $scimUser->first_name = 'Alex';
        $scimUser->last_name = 'Example';
        $scimUser->username = 'scim-ietf-'.uniqid();
        $scimUser->email = $scimUser->username.'@example.com';
        $scimUser->activated = 1;
        $scimUser->save();

        $log = Actionlog::where('item_id', $scimUser->id)
            ->where('action_type', 'create')
            ->latest('id')
            ->first();

        $this->assertNotNull($log, 'SCIMUser save must produce a create Actionlog');
        $this->assertSame(
            User::class,
            $log->item_type,
            'action_logs.item_type must store the canonical User class, not SCIMUser (issue #20118)',
        );
    }

    #[Test]
    public function updating_a_scim_user_logs_item_type_as_user_not_scim_user(): void
    {
        Passport::actingAs(User::factory()->superuser()->create());

        // Save through the SCIM subclass so the Loggable observer
        // records `$this::class` as SCIMUser without the fix.
        $scimUser = new SCIMUser;
        $scimUser->first_name = 'Alex';
        $scimUser->last_name = 'Example';
        $scimUser->username = 'scim-ietf-'.uniqid();
        $scimUser->email = $scimUser->username.'@example.com';
        $scimUser->activated = 1;
        $scimUser->save();

        $scimUser->first_name = 'Alexander';
        $scimUser->save();

        $updateLog = Actionlog::where('item_id', $scimUser->id)
            ->where('action_type', 'update')
            ->latest('id')
            ->first();

        $this->assertNotNull($updateLog, 'SCIMUser update must produce an update Actionlog');
        $this->assertSame(User::class, $updateLog->item_type);
    }

    #[Test]
    public function migration_rewrites_historical_scim_user_rows_to_user(): void
    {
        $user = User::factory()->create();

        // Simulate rows that pre-date the normalization fix by
        // bypassing the model layer.
        $badItemId = DB::table('action_logs')->insertGetId([
            'item_type' => 'App\\Models\\SCIMUser',
            'item_id' => $user->id,
            'action_type' => 'create',
            'created_at' => now(),
            'action_date' => now(),
        ]);
        $badTargetId = DB::table('action_logs')->insertGetId([
            'item_type' => 'App\\Models\\User',
            'item_id' => $user->id,
            'target_type' => 'App\\Models\\SCIMUser',
            'target_id' => $user->id,
            'action_type' => 'update',
            'created_at' => now(),
            'action_date' => now(),
        ]);

        // Re-run the migration against the two synthetic rows. The
        // migration is idempotent (plain UPDATE with an equality
        // WHERE clause), so re-running on an already-migrated
        // database is a no-op.
        $migration = require database_path('migrations/2026_11_22_163855_normalize_scim_subclass_types_on_action_logs.php');
        $migration->up();

        $this->assertSame(
            User::class,
            DB::table('action_logs')->where('id', $badItemId)->value('item_type'),
        );
        $this->assertSame(
            User::class,
            DB::table('action_logs')->where('id', $badTargetId)->value('target_type'),
        );
    }
}
