<?php

namespace App\Enums;

/**
 * Passport OAuth scopes the MCP server recognizes.
 *
 * Two layers of enforcement:
 *
 *   - Session (McpScope::Session = `mcp:use`) is required to open
 *     an MCP session with the server. The session gate (handshake
 *     middleware) accepts ANY scope defined in this enum, including
 *     Session, so a client with only the framework-standard `mcp:use`
 *     scope can at least connect and call tools/list to discover
 *     what capability scopes each tool actually needs.
 *
 *   - Capability scopes (Read / Write / Delete / Checkout / Checkin /
 *     Superadmin) gate specific tool calls. Per-tool checks do exact
 *     match, so a token with Session alone cannot call ANY tool.
 *     Clients that discover per-tool scope requirements via
 *     `_meta.securitySchemes` on tools/list can then re-authorize
 *     for the specific capability scopes they need.
 *
 * There is no superset relationship between capability scopes.
 * `mcp:write` does not grant `mcp:read`. `mcp:superadmin` is not a
 * meta-scope that implicitly includes the others. Admins who want
 * full-capability tokens must explicitly grant every scope at token
 * issuance time.
 *
 * Capability mapping (current rollout):
 *
 *   Read        list_*, show_*, get_user_assets, show_user_permissions,
 *               find_similar_models, list_history, list_uploads,
 *               list_asset_notes, list_maintenances, list_maintenance_types,
 *               get_current_user, get_activity_log
 *   Write       create_*, update_*, update_profile, audit_asset,
 *               add_asset_note, upload_asset_image, reset_2fa,
 *               send_password_reset
 *   Delete      delete_*, restore_asset, restore_user
 *   Checkout    checkout_*, offboard_user, transfer_user_assets
 *   Checkin     checkin_*
 *   Superadmin  create_group, update_group, delete_group, list_groups,
 *               show_group
 *
 * reset_2fa and send_password_reset sit in Write because they gate on
 * `users.update` today, not superadmin. update_user holds Write even
 * though its group_ids branch still requires isSuperUser() at the
 * permission layer. The scope is about the tool's primary verb. Fine-
 * grained permission checks run after the scope passes.
 */
enum McpScope: string
{
    case Session = 'mcp:use';
    case Read = 'mcp:read';
    case Write = 'mcp:write';
    case Delete = 'mcp:delete';
    case Checkout = 'mcp:checkout';
    case Checkin = 'mcp:checkin';
    case Superadmin = 'mcp:superadmin';

    /**
     * Short translated description for the OAuth consent + PAT
     * issuance UIs. Keeps the user-facing phrasing in one place.
     */
    public function description(): string
    {
        return match ($this) {
            self::Session => trans('mcp.scope_session_description'),
            self::Read => trans('mcp.scope_read_description'),
            self::Write => trans('mcp.scope_write_description'),
            self::Delete => trans('mcp.scope_delete_description'),
            self::Checkout => trans('mcp.scope_checkout_description'),
            self::Checkin => trans('mcp.scope_checkin_description'),
            self::Superadmin => trans('mcp.scope_superadmin_description'),
        };
    }

    /**
     * All scope values as a flat array. Passed to
     * Passport::tokensCan() at boot to register them with the OAuth
     * server so tokens can request them.
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $c) => $c->value, self::cases());
    }

    /**
     * All scopes keyed by value, mapped to translated descriptions.
     * Shape that Passport::tokensCan() expects.
     *
     * @return array<string, string>
     */
    public static function forPassport(): array
    {
        $out = [];
        foreach (self::cases() as $case) {
            $out[$case->value] = $case->description();
        }

        return $out;
    }
}
