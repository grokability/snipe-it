<?php

namespace App\Mcp\Tools;

use App\Enums\McpScope;
use App\Events\CheckoutableCheckedIn;
use App\Events\CheckoutableCheckedOut;
use App\Mcp\Traits\SnipeMcpTool;
use App\Models\AccessoryCheckout;
use App\Models\Asset;
use App\Models\CheckoutAcceptance;
use App\Models\LicenseSeat;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Database\Eloquent\Collection;
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
 * Atomic transfer of all assigned items from one user to another.
 * Preview the full move (assets, accessories, license seats) and execute
 * every step inside one DB transaction, or just return the preview when
 * dry_run is true.
 *
 * Default is dry_run=true. Callers must explicitly pass dry_run=false
 * to make any changes. Identifier flexibility mirrors OffboardUserTool:
 * either username or first_name+last_name is accepted on each side, and
 * the tool returns the match set for the AI to disambiguate when a
 * name query returns multiple users.
 */
#[Name('transfer_user_assets')]
#[Title('Transfer User Assets')]
#[Description('Atomically transfer every asset, accessory, and license seat from one user to another. Defaults to dry_run so no changes are made without explicit confirmation.')]
class TransferAssetsTool extends Tool
{
    use SnipeMcpTool;

    public function handle(Request $request): ResponseFactory
    {
        if ($denied = $this->scopeDenied(McpScope::Checkout)) {
            return $denied;
        }

        $request->validate([
            'from_username' => 'nullable|string|max:191',
            'from_first_name' => 'nullable|string|max:191',
            'from_last_name' => 'nullable|string|max:191',
            'to_username' => 'nullable|string|max:191',
            'to_first_name' => 'nullable|string|max:191',
            'to_last_name' => 'nullable|string|max:191',
            'dry_run' => 'nullable|boolean',
            'checkin_non_reassignable_seats' => 'nullable|boolean',
            'note' => 'nullable|string|max:65535',
        ]);

        $dryRun = $request->boolean('dry_run', true);

        $fromResult = $this->resolveUser(
            $request->get('from_username'),
            $request->get('from_first_name'),
            $request->get('from_last_name'),
            'from'
        );
        if ($fromResult instanceof ResponseFactory) {
            return $fromResult;
        }
        $from = $fromResult;

        $toResult = $this->resolveUser(
            $request->get('to_username'),
            $request->get('to_first_name'),
            $request->get('to_last_name'),
            'to'
        );
        if ($toResult instanceof ResponseFactory) {
            return $toResult;
        }
        $to = $toResult;

        if ($from->id === $to->id) {
            return Response::make(Response::error(trans('mcp.transfer_same_user')));
        }

        // Require view on the source user.
        if (! Gate::allows('view', $from)) {
            return Response::make(Response::error(trans('general.generic_model_not_found', ['model' => trans('general.user')])));
        }

        // Query Asset directly instead of via $from->assets() so
        // the collection is typed as Collection<int, Asset> for the
        // downstream loop. The relation method on User has no return
        // type and loses Asset narrowing.
        $assets = Asset::where('assigned_type', User::class)
            ->where('assigned_to', $from->id)
            ->withTrashed()
            ->orderBy('id')
            ->get();
        $accessoryCheckouts = AccessoryCheckout::with('accessory')
            ->where('assigned_type', User::class)
            ->where('assigned_to', $from->id)
            ->get();
        $allLicenseSeats = LicenseSeat::with('license')->where('assigned_to', $from->id)->get();
        // Non-reassignable seats stay with the source user by design
        // (matches UserItemTransferController::transferLicenseSeats skip
        // response). Put them in a dedicated skipped list so the caller
        // sees the seats that will NOT move, rather than silently dropping
        // them from the preview.
        $licenseSeats = $allLicenseSeats->filter(fn (LicenseSeat $s) => (bool) $s->license?->reassignable)->values();
        $skippedLicenseSeats = $allLicenseSeats->reject(fn (LicenseSeat $s) => (bool) $s->license?->reassignable)->values();

        $preview = [
            'from' => $this->userSummary($from),
            'to' => $this->userSummary($to),
            'assets_to_transfer' => $assets->map(fn (Asset $a) => [
                'id' => $a->id,
                'asset_tag' => $a->asset_tag,
                'name' => $a->name,
            ])->all(),
            'accessory_checkouts_to_transfer' => $accessoryCheckouts->map(fn (AccessoryCheckout $c) => [
                'checkout_id' => $c->id,
                'accessory_id' => $c->accessory_id,
                'accessory_name' => $c->accessory?->name,
            ])->all(),
            'license_seats_to_transfer' => $licenseSeats->map(fn (LicenseSeat $s) => [
                'seat_id' => $s->id,
                'license_id' => $s->license_id,
                'license_name' => $s->license?->name,
            ])->all(),
            'license_seats_skipped' => $skippedLicenseSeats->map(fn (LicenseSeat $s) => [
                'seat_id' => $s->id,
                'license_id' => $s->license_id,
                'license_name' => $s->license?->name,
                'reason' => 'non_reassignable',
            ])->all(),
        ];

        if ($dryRun) {
            return $this->structuredResponse(
                trans('mcp.transfer_dry_run_summary', [
                    'from' => $from->username,
                    'to' => $to->username,
                    'assets' => count($preview['assets_to_transfer']),
                    'accessories' => count($preview['accessory_checkouts_to_transfer']),
                    'seats' => count($preview['license_seats_to_transfer']),
                ]),
                [
                    'success' => true,
                    'dry_run' => true,
                    'message' => trans('mcp.transfer_dry_run_message'),
                    'preview' => $preview,
                ]
            );
        }

        // If the source holds seats on a license whose reassignable flag
        // is off, and the MCP caller has not told us what to do with them,
        // stop and return a prompt so the caller can decide. Checking such
        // a seat in sets license_seats.unreassignable_seat to true
        // (LicenseCheckinController does this write), which excludes the
        // seat from free-seat queries from then on. Default to no action
        // until the caller is explicit.
        $hasNonReassignable = count($preview['license_seats_skipped']) > 0;
        if ($hasNonReassignable && ! $request->has('checkin_non_reassignable_seats')) {
            $names = implode(', ', array_filter(array_column($preview['license_seats_skipped'], 'license_name')));

            return $this->structuredResponse(
                trans('mcp.transfer_non_reassignable_prompt', [
                    'count' => count($preview['license_seats_skipped']),
                    'names' => $names,
                ]),
                [
                    'success' => false,
                    'requires_decision' => 'checkin_non_reassignable_seats',
                    'non_reassignable_license_seats' => $preview['license_seats_skipped'],
                    'preview' => $preview,
                ]
            );
        }
        $checkInNonReassignable = $request->boolean('checkin_non_reassignable_seats', false);

        $note = $request->get('note');
        $actor = auth()->user();
        $failures = [];

        try {
            DB::transaction(function () use ($assets, $accessoryCheckouts, $licenseSeats, $skippedLicenseSeats, $checkInNonReassignable, $from, $to, $note, $actor, &$failures) {
                foreach ($assets as $asset) {
                    if (! Gate::allows('checkin', $asset) || ! Gate::allows('checkout', $asset)) {
                        $failures[] = "asset {$asset->asset_tag}: permission denied";

                        continue;
                    }
                    if (! $asset->canCheckoutTo($to)) {
                        $failures[] = "asset {$asset->asset_tag}: target user is not allowed to receive items from this asset's company";

                        continue;
                    }
                    if (! $asset->checkIn($actor, $note)) {
                        $failures[] = "asset {$asset->asset_tag}: checkin from source failed";

                        continue;
                    }
                    if (! $asset->checkOut($to, $actor, date('Y-m-d H:i:s'), null, $note, null, $asset->location_id)) {
                        $failures[] = "asset {$asset->asset_tag}: checkout to target failed";
                    }
                }

                foreach ($accessoryCheckouts as $checkout) {
                    $accessory = $checkout->accessory;
                    if ($accessory && (! Gate::allows('checkin', $accessory) || ! Gate::allows('checkout', $accessory))) {
                        $failures[] = "accessory {$accessory->name}: permission denied";

                        continue;
                    }
                    if ($accessory && ! $accessory->canCheckoutTo($to)) {
                        $failures[] = "accessory {$accessory->name}: target user is not allowed to receive items from this accessory's company";

                        continue;
                    }
                    $checkout->assigned_to = $to->id;
                    if (! $checkout->save()) {
                        $failures[] = "accessory checkout {$checkout->id}: reassignment failed";

                        continue;
                    }
                    // Fire the same check-in and check-out events the web
                    // transfer fires so the move shows up in the activity
                    // log the same way a direct accessory check-in does.
                    event(new CheckoutableCheckedIn($accessory, $from, $actor, $note));
                    event(new CheckoutableCheckedOut($accessory, $to, $actor, $note, [], 1, false));
                }

                foreach ($licenseSeats as $seat) {
                    $license = $seat->license;
                    if (! $license || ! Gate::allows('checkin', $license) || ! Gate::allows('checkout', $license)) {
                        $failures[] = "license {$license->name}: permission denied";

                        continue;
                    }
                    if (! $license->canCheckoutTo($to)) {
                        $failures[] = "license {$license->name}: target user is not allowed to receive items from this license's company";

                        continue;
                    }
                    // Mirror the web transfer: delete pending acceptances on
                    // the source side, then check in and check out as two
                    // saves so the activity log records both halves.
                    CheckoutAcceptance::pending()
                        ->where('checkoutable_type', LicenseSeat::class)
                        ->where('checkoutable_id', $seat->id)
                        ->where('assigned_to_id', $from->id)
                        ->get()
                        ->each(fn ($acceptance) => $acceptance->delete());

                    $seat->assigned_to = null;
                    if (! $seat->save()) {
                        $failures[] = "license seat {$seat->id}: check-in failed";

                        continue;
                    }
                    event(new CheckoutableCheckedIn($seat, $from, $actor, $note));

                    $seat->assigned_to = $to->id;
                    if (! $seat->save()) {
                        $failures[] = "license seat {$seat->id}: check-out failed";

                        continue;
                    }
                    event(new CheckoutableCheckedOut($seat, $to, $actor, $note, [], 1, false));
                }

                // Non-reassignable seats: check them in if the caller opted
                // in, otherwise leave them with the source user. The matching
                // bucket in the response tells the caller which path was
                // taken. Check-in only (no corresponding check-out), with
                // unreassignable_seat set to true so free-seat queries stop
                // offering the seat (same write LicenseCheckinController
                // does).
                if ($checkInNonReassignable) {
                    foreach ($skippedLicenseSeats as $seat) {
                        $license = $seat->license;
                        if (! $license || ! Gate::allows('checkin', $license)) {
                            $failures[] = "license {$license->name}: permission denied for check-in";

                            continue;
                        }
                        CheckoutAcceptance::pending()
                            ->where('checkoutable_type', LicenseSeat::class)
                            ->where('checkoutable_id', $seat->id)
                            ->where('assigned_to_id', $from->id)
                            ->get()
                            ->each(fn ($acceptance) => $acceptance->delete());

                        $seat->assigned_to = null;
                        $seat->unreassignable_seat = true;
                        if (! $seat->save()) {
                            $failures[] = "license seat {$seat->id}: check-in failed";

                            continue;
                        }
                        event(new CheckoutableCheckedIn($seat, $from, $actor, $note));
                    }
                }

                if (! empty($failures)) {
                    throw new RuntimeException('transfer partial failure');
                }
            });
        } catch (Throwable $e) {
            return Response::make(Response::error(trans('mcp.transfer_failed', [
                'failures' => implode('; ', $failures ?: [$e->getMessage()]),
            ])));
        }

        $completedSummary = trans('mcp.transfer_completed_summary', [
            'from' => $from->username,
            'to' => $to->username,
            'assets' => count($preview['assets_to_transfer']),
            'accessories' => count($preview['accessory_checkouts_to_transfer']),
            'seats' => count($preview['license_seats_to_transfer']),
        ]);
        if ($preview['license_seats_skipped'] !== []) {
            $nonReassignableNames = implode(', ', array_filter(array_column($preview['license_seats_skipped'], 'license_name')));
            $completedSummary .= ' '.trans($checkInNonReassignable ? 'mcp.transfer_checked_in_suffix' : 'mcp.transfer_skipped_suffix', [
                'count' => count($preview['license_seats_skipped']),
                'names' => $nonReassignableNames,
            ]);
        }

        return $this->structuredResponse(
            $completedSummary,
            [
                'success' => true,
                'dry_run' => false,
                'message' => trans('mcp.transfer_completed_message', ['from' => $from->username, 'to' => $to->username]),
                'executed' => $preview,
                'non_reassignable_license_seats_checked_in' => $checkInNonReassignable ? $preview['license_seats_skipped'] : [],
                'non_reassignable_license_seats_skipped' => $checkInNonReassignable ? [] : $preview['license_seats_skipped'],
            ]
        );
    }

    /**
     * Resolve a user by username or by first_name+last_name. Returns a
     * User instance on success, or a ResponseFactory with the error /
     * ambiguity payload for the caller to pass straight through.
     */
    private function resolveUser(?string $username, ?string $firstName, ?string $lastName, string $side): User|ResponseFactory
    {
        if ($username !== null && $username !== '') {
            $matches = User::where('username', $username)->get();
        } elseif (($firstName !== null && $firstName !== '') || ($lastName !== null && $lastName !== '')) {
            $query = User::query();
            if ($firstName !== null && $firstName !== '') {
                $query->where('first_name', $firstName);
            }
            if ($lastName !== null && $lastName !== '') {
                $query->where('last_name', $lastName);
            }
            $matches = $query->get();
        } else {
            return Response::make(Response::error(trans('mcp.transfer_identifier_required', ['side' => $side])));
        }

        if ($matches->isEmpty()) {
            return Response::make(Response::error(trans('mcp.transfer_user_not_found', ['side' => $side])));
        }

        if ($matches->count() > 1) {
            return $this->ambiguousUserResponse($matches, $side);
        }

        return $matches->first();
    }

    /**
     * @param  Collection<int, User>  $matches
     */
    private function ambiguousUserResponse(Collection $matches, string $side): ResponseFactory
    {
        return $this->structuredResponse(
            trans('mcp.transfer_ambiguous_user', ['side' => $side]),
            [
                'success' => false,
                'error' => 'ambiguous_user',
                'side' => $side,
                'message' => trans('mcp.transfer_ambiguous_user', ['side' => $side]),
                'matches' => $matches->map(fn (User $u) => [
                    'id' => $u->id,
                    'username' => $u->username,
                    'full_name' => $u->full_name,
                    'email' => $u->email,
                ])->all(),
            ]
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function userSummary(User $user): array
    {
        return [
            'id' => $user->id,
            'username' => $user->username,
            'full_name' => $user->full_name,
        ];
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'from_username' => $schema->string()->description('Username of the user whose items will be transferred. Preferred because unique. One of from_username or from_first_name+from_last_name is required.'),
            'from_first_name' => $schema->string()->description('First name of the source user. Combine with from_last_name when the username is not known.'),
            'from_last_name' => $schema->string()->description('Last name of the source user.'),
            'to_username' => $schema->string()->description('Username of the user who will receive the items. Same rules as from_username.'),
            'to_first_name' => $schema->string()->description('First name of the target user.'),
            'to_last_name' => $schema->string()->description('Last name of the target user.'),
            'dry_run' => $schema->boolean()->description('When true (default) return the preview without making changes. Set false to execute.'),
            'checkin_non_reassignable_seats' => $schema->boolean()->description('Decision for license seats attached to non-reassignable licenses. When the source user holds any such seats, call the tool once without this parameter to see the prompt, then call again with true to check them in (seat becomes unavailable and cannot be checked out again) or false to leave them with the source user.'),
            'note' => $schema->string()->description('Optional note recorded on each asset checkin and checkout.'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'success' => $schema->boolean()->description('True if the (dry-run preview or actual) transfer reported no errors'),
            'dry_run' => $schema->boolean()->description('Whether this response was a preview (true) or an actual execution (false)'),
            'message' => $schema->string()->description('Human-readable result message')->required(),
            'preview' => $schema->object()->description('Structured preview of what would move (present when dry_run=true)')->nullable(),
            'executed' => $schema->object()->description('Structured record of what did move (present when dry_run=false)')->nullable(),
            'matches' => $schema->array()->description('When an ambiguous user is encountered, the list of candidate users for the caller to pick from')->nullable(),
        ];
    }
}
