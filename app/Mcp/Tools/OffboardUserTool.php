<?php

namespace App\Mcp\Tools;

use App\Enums\McpScope;
use App\Mcp\Traits\HasMcpScope;
use App\Models\AccessoryCheckout;
use App\Models\Asset;
use App\Models\LicenseSeat;
use App\Models\Location;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;
use RuntimeException;
use Throwable;

/**
 * Atomic offboarding for a single user. Preview the full blast radius
 * (assigned assets, accessories, license seats, users managed by this
 * user, locations managed by this user) and execute every step inside
 * one DB transaction, or just return the preview when dry_run is true.
 *
 * Default is dry_run=true. Callers must explicitly pass dry_run=false
 * to make any changes.
 *
 * Final step is a soft-delete on the user (sets deleted_at), not a
 * flip of the `activated` column. In Snipe-IT `activated` only means
 * "can log in through the web UI" and many installs never set it on
 * real users (orgs that only use the system for asset tracking and
 * never have end-users log in), so clearing it is a no-op for those
 * orgs and does not indicate "no longer with the company." Soft-delete
 * is the correct exit signal and removes the user from active lists
 * while preserving history.
 *
 * Per-item permission checks still apply. If the acting user lacks
 * checkin / update permission for any touched row, the whole
 * transaction rolls back rather than offboard partially.
 */
#[Name('offboard_user')]
#[Title('Offboard User')]
#[Description('Atomically offboard a user: preview or execute check-in of every assigned asset/accessory/license seat, clear manager references on dependent users and locations, and deactivate the account. Defaults to dry_run so no changes are made without explicit confirmation.')]
class OffboardUserTool extends Tool
{
    use HasMcpScope;

    public function handle(Request $request): ResponseFactory
    {
        if ($denied = $this->scopeDenied(McpScope::Checkout)) {
            return $denied;
        }

        $request->validate([
            'username' => 'nullable|string|max:191',
            'first_name' => 'nullable|string|max:191',
            'last_name' => 'nullable|string|max:191',
            'dry_run' => 'nullable|boolean',
            'note' => 'nullable|string|max:65535',
        ]);

        $dryRun = $request->boolean('dry_run', true);

        // Resolve the target user. Username wins when supplied because it
        // is unique. first_name + last_name is a convenience for the AI
        // when the human spoke a real name rather than an account id, but
        // may match multiple users: in that case return the ambiguous
        // match set so the AI can ask the human which one they meant.
        if ($request->filled('username')) {
            $matches = User::where('username', $request->get('username'))->get();
        } elseif ($request->filled('first_name') || $request->filled('last_name')) {
            $query = User::query();
            if ($request->filled('first_name')) {
                $query->where('first_name', $request->get('first_name'));
            }
            if ($request->filled('last_name')) {
                $query->where('last_name', $request->get('last_name'));
            }
            $matches = $query->get();
        } else {
            return Response::make(Response::error(trans('mcp.offboard_identifier_required')));
        }

        if ($matches->isEmpty()) {
            return Response::make(Response::error(trans('mcp.user_not_found')));
        }

        if ($matches->count() > 1) {
            return Response::make(
                Response::text(trans('mcp.offboard_ambiguous_user'))
            )->withStructuredContent([
                'success' => false,
                'error' => 'ambiguous_user',
                'message' => trans('mcp.offboard_ambiguous_user'),
                'matches' => $matches->map(fn (User $u) => [
                    'id' => $u->id,
                    'username' => $u->username,
                    'full_name' => trim($u->first_name.' '.$u->last_name),
                    'email' => $u->email,
                ])->all(),
            ]);
        }

        $user = $matches->first();

        if (! Gate::allows('update', $user)) {
            return Response::make(Response::error(trans('mcp.unauthorized')));
        }

        // Query Asset directly instead of via $user->assets() so the
        // collection is typed as Collection<int, Asset> for the
        // downstream loop. The relation method on User has no return
        // type and loses Asset narrowing.
        $assets = Asset::where('assigned_type', User::class)
            ->where('assigned_to', $user->id)
            ->withTrashed()
            ->orderBy('id')
            ->get();
        $accessoryCheckouts = AccessoryCheckout::with('accessory')
            ->where('assigned_type', User::class)
            ->where('assigned_to', $user->id)
            ->get();
        $licenseSeats = LicenseSeat::with('license')->where('assigned_to', $user->id)->get();
        $managedUsers = User::where('manager_id', $user->id)->get();
        $managedLocations = Location::where('manager_id', $user->id)->get();

        $preview = [
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'full_name' => trim($user->first_name.' '.$user->last_name),
            ],
            'assets_to_checkin' => $assets->map(fn (Asset $a) => [
                'id' => $a->id,
                'asset_tag' => $a->asset_tag,
                'name' => $a->name,
            ])->all(),
            'accessory_checkouts_to_return' => $accessoryCheckouts->map(fn (AccessoryCheckout $c) => [
                'checkout_id' => $c->id,
                'accessory_id' => $c->accessory_id,
                'accessory_name' => $c->accessory?->name,
            ])->all(),
            'license_seats_to_release' => $licenseSeats->map(fn (LicenseSeat $s) => [
                'seat_id' => $s->id,
                'license_id' => $s->license_id,
                'license_name' => $s->license?->name,
            ])->all(),
            'managed_users_to_unparent' => $managedUsers->map(fn (User $u) => [
                'id' => $u->id,
                'username' => $u->username,
            ])->all(),
            'managed_locations_to_unparent' => $managedLocations->map(fn (Location $l) => [
                'id' => $l->id,
                'name' => $l->name,
            ])->all(),
            'will_soft_delete_user' => true,
        ];

        if ($dryRun) {
            return Response::make(
                Response::text(trans('mcp.offboard_dry_run_summary', [
                    'username' => $user->username,
                    'assets' => count($preview['assets_to_checkin']),
                    'accessories' => count($preview['accessory_checkouts_to_return']),
                    'seats' => count($preview['license_seats_to_release']),
                ]))
            )->withStructuredContent([
                'success' => true,
                'dry_run' => true,
                'message' => trans('mcp.offboard_dry_run_message'),
                'preview' => $preview,
            ]);
        }

        $note = $request->get('note');
        $actor = auth()->user();
        $failures = [];

        try {
            DB::transaction(function () use ($assets, $accessoryCheckouts, $licenseSeats, $managedUsers, $managedLocations, $user, $note, $actor, &$failures) {
                foreach ($assets as $asset) {
                    if (! Gate::allows('checkin', $asset)) {
                        $failures[] = "asset {$asset->asset_tag}: permission denied";

                        continue;
                    }
                    if (! $asset->checkIn($actor, $note)) {
                        $failures[] = "asset {$asset->asset_tag}: checkin failed";
                    }
                }

                foreach ($accessoryCheckouts as $checkout) {
                    if (! $checkout->delete()) {
                        $failures[] = "accessory checkout {$checkout->id}: delete failed";
                    }
                }

                foreach ($licenseSeats as $seat) {
                    $seat->assigned_to = null;
                    if (! $seat->save()) {
                        $failures[] = "license seat {$seat->id}: release failed";
                    }
                }

                User::whereIn('id', $managedUsers->pluck('id'))->update(['manager_id' => null]);
                Location::whereIn('id', $managedLocations->pluck('id'))->update(['manager_id' => null]);

                if (! $user->delete()) {
                    $failures[] = 'user soft-delete failed';
                }

                if (! empty($failures)) {
                    throw new RuntimeException('offboard partial failure');
                }
            });
        } catch (Throwable $e) {
            return Response::make(Response::error(trans('mcp.offboard_failed', [
                'failures' => implode('; ', $failures ?: [$e->getMessage()]),
            ])));
        }

        return Response::make(
            Response::text(trans('mcp.offboard_completed_summary', [
                'username' => $user->username,
                'assets' => count($preview['assets_to_checkin']),
                'accessories' => count($preview['accessory_checkouts_to_return']),
                'seats' => count($preview['license_seats_to_release']),
            ]))
        )->withStructuredContent([
            'success' => true,
            'dry_run' => false,
            'message' => trans('mcp.offboard_completed_message', ['username' => $user->username]),
            'executed' => $preview,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'username' => $schema->string()->description('Username of the employee to offboard. Preferred because it is unique. One of username or first_name+last_name is required.'),
            'first_name' => $schema->string()->description('First name. Combine with last_name when username is not known. If multiple users match, the tool returns the match set for the caller to disambiguate.'),
            'last_name' => $schema->string()->description('Last name. See first_name for disambiguation rules.'),
            'dry_run' => $schema->boolean()->description('When true (default) return the preview without making changes. Set false to execute.'),
            'note' => $schema->string()->description('Optional note recorded on each asset checkin'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'success' => $schema->boolean()->description('True if the (dry-run preview or actual) offboarding reported no errors'),
            'dry_run' => $schema->boolean()->description('Whether this response was a preview (true) or an actual execution (false)'),
            'message' => $schema->string()->description('Human-readable result message')->required(),
            'preview' => $schema->object()->description('Structured preview of what would change (present when dry_run=true)'),
            'executed' => $schema->object()->description('Structured record of what did change (present when dry_run=false)'),
        ];
    }
}
