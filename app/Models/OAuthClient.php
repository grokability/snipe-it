<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Laravel\Passport\Client as PassportClient;

/**
 * Snipe-IT OAuth client with an approval workflow on top of Passport's
 * base Client. The status + metadata columns are populated by the DCR
 * handler and read by the authorize/token middleware so a pending or
 * rejected client can't complete an OAuth exchange.
 *
 * Registered in AuthServiceProvider via Passport::useClientModel() so
 * every Passport code path (ClientRepository::createAuthorizationCodeGrantClient,
 * legacy client:create artisan commands, laravel/mcp DCR) reads and
 * writes through this subclass.
 *
 * @property string $status
 * @property ?\Carbon\Carbon $approved_at
 * @property ?string $approved_by_id
 * @property ?string $registered_ip
 * @property ?string $rejected_reason
 */
class OAuthClient extends PassportClient
{
    public const STATUS_APPROVED = 'approved';

    public const STATUS_PENDING = 'pending';

    public const STATUS_REJECTED = 'rejected';

    protected $casts = [
        'grant_types' => 'array',
        'scopes' => 'array',
        'redirect_uris' => 'array',
        'personal_access_client' => 'bool',
        'password_client' => 'bool',
        'revoked' => 'bool',
        'approved_at' => 'datetime',
    ];

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    /**
     * The first redirect URI, normalized for display on the MCP consent
     * screen and the admin approval queue. Walks the array-cast
     * redirect_uris first, then the legacy single-column redirect
     * field for pre-Passport-13 rows that predate the UUID migration.
     */
    protected function primaryRedirectUri(): Attribute
    {
        return Attribute::get(function (): ?string {
            $uris = $this->getAttribute('redirect_uris');
            if (is_array($uris) && isset($uris[0]) && is_string($uris[0])) {
                return $uris[0];
            }
            $legacy = $this->getAttribute('redirect');

            return is_string($legacy) && $legacy !== '' ? $legacy : null;
        });
    }
}
