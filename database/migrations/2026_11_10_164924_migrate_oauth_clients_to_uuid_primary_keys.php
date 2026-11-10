<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Passport v13's Client model uses the HasUuids trait so client IDs are
 * generated as UUIDs on save. The v12 schema stores oauth_clients.id
 * (and the client_id FKs on oauth_access_tokens, oauth_auth_codes,
 * oauth_personal_access_clients) as bigint auto-increment. INSERTs from
 * Passport v13 fail with a duplicate-key collision because the UUID
 * string truncates into the bigint column.
 *
 * This migration swaps those four id/FK columns from bigint to char(36)
 * and backfills existing clients with freshly minted UUIDs. Other v12
 * columns (user_id, redirect, personal_access_client, password_client)
 * stay as-is. Passport v13's ClientRepository::create() is column-list-
 * aware and falls back to those shapes when redirect_uris and
 * grant_types do not exist.
 *
 * Driver gating: the column-type swaps use MariaDB/MySQL-specific
 * CHANGE/MODIFY syntax, so the whole migration is a no-op on other
 * drivers. SQLite is dynamically typed and accepts UUID strings into
 * INT columns at runtime, so the test suite never needs this.
 *
 * Idempotency: MySQL DDL is not transactional, so a prior partial run
 * can leave shadow columns in place. Every Schema add is guarded by a
 * hasColumn check so a retry after a failure converges cleanly.
 *
 * Orphans: dependent rows (tokens, auth codes, personal-access-client
 * rows) whose client_id has no matching oauth_clients.id row cannot be
 * backfilled with a UUID and would fail the final NOT NULL constraint.
 * Those rows are already functionally dead so they are pruned before
 * the rename.
 *
 * Table prefixes: raw ALTER statements are routed through
 * DB::getTablePrefix() so sites running Snipe-IT with DB_PREFIX set
 * target the correct physical table name.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        $prefix = DB::getTablePrefix();

        DB::table('oauth_access_tokens')
            ->whereNotIn('client_id', fn ($q) => $q->select('id')->from('oauth_clients'))
            ->delete();
        DB::table('oauth_auth_codes')
            ->whereNotIn('client_id', fn ($q) => $q->select('id')->from('oauth_clients'))
            ->delete();
        DB::table('oauth_personal_access_clients')
            ->whereNotIn('client_id', fn ($q) => $q->select('id')->from('oauth_clients'))
            ->delete();

        $idMap = [];
        foreach (DB::table('oauth_clients')->pluck('id') as $oldId) {
            $idMap[(int) $oldId] = (string) Str::orderedUuid();
        }

        if (! Schema::hasColumn('oauth_clients', 'id_uuid')) {
            Schema::table('oauth_clients', function (Blueprint $table) {
                $table->char('id_uuid', 36)->nullable()->after('id');
            });
        }
        if (! Schema::hasColumn('oauth_access_tokens', 'client_id_uuid')) {
            Schema::table('oauth_access_tokens', function (Blueprint $table) {
                $table->char('client_id_uuid', 36)->nullable()->after('client_id');
            });
        }
        if (! Schema::hasColumn('oauth_auth_codes', 'client_id_uuid')) {
            Schema::table('oauth_auth_codes', function (Blueprint $table) {
                $table->char('client_id_uuid', 36)->nullable()->after('client_id');
            });
        }
        if (! Schema::hasColumn('oauth_personal_access_clients', 'client_id_uuid')) {
            Schema::table('oauth_personal_access_clients', function (Blueprint $table) {
                $table->char('client_id_uuid', 36)->nullable()->after('client_id');
            });
        }

        foreach ($idMap as $oldId => $newUuid) {
            DB::table('oauth_clients')->where('id', $oldId)->update(['id_uuid' => $newUuid]);
            DB::table('oauth_access_tokens')->where('client_id', $oldId)->update(['client_id_uuid' => $newUuid]);
            DB::table('oauth_auth_codes')->where('client_id', $oldId)->update(['client_id_uuid' => $newUuid]);
            DB::table('oauth_personal_access_clients')->where('client_id', $oldId)->update(['client_id_uuid' => $newUuid]);
        }

        if (Schema::hasColumn('oauth_clients', 'id')) {
            // MySQL refuses DROP PRIMARY KEY on an AUTO_INCREMENT column,
            // so strip the AUTO_INCREMENT first via MODIFY.
            DB::statement("ALTER TABLE {$prefix}oauth_clients MODIFY id BIGINT UNSIGNED NOT NULL");
            Schema::table('oauth_clients', function (Blueprint $table) {
                $table->dropPrimary('primary');
                $table->dropColumn('id');
            });
            DB::statement("ALTER TABLE {$prefix}oauth_clients CHANGE id_uuid id CHAR(36) NOT NULL FIRST");
            Schema::table('oauth_clients', function (Blueprint $table) {
                $table->primary('id');
            });
        }

        if (Schema::hasColumn('oauth_access_tokens', 'client_id_uuid')) {
            Schema::table('oauth_access_tokens', function (Blueprint $table) {
                $table->dropColumn('client_id');
            });
            DB::statement("ALTER TABLE {$prefix}oauth_access_tokens CHANGE client_id_uuid client_id CHAR(36) NOT NULL");
        }

        if (Schema::hasColumn('oauth_auth_codes', 'client_id_uuid')) {
            Schema::table('oauth_auth_codes', function (Blueprint $table) {
                $table->dropColumn('client_id');
            });
            DB::statement("ALTER TABLE {$prefix}oauth_auth_codes CHANGE client_id_uuid client_id CHAR(36) NOT NULL");
        }

        if (Schema::hasColumn('oauth_personal_access_clients', 'client_id_uuid')) {
            Schema::table('oauth_personal_access_clients', function (Blueprint $table) {
                $table->dropColumn('client_id');
            });
            DB::statement("ALTER TABLE {$prefix}oauth_personal_access_clients CHANGE client_id_uuid client_id CHAR(36) NOT NULL");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        $prefix = DB::getTablePrefix();

        // Mint sequential bigint IDs for existing UUID clients so related
        // rows can be re-linked. Done in PHP rather than via
        // bigIncrements (which would clash with the existing UUID PK on
        // the table).
        if (! Schema::hasColumn('oauth_clients', 'id_old')) {
            Schema::table('oauth_clients', function (Blueprint $table) {
                $table->unsignedBigInteger('id_old')->nullable()->after('id');
            });
        }

        $counter = 0;
        $uuidToNewBigint = [];
        foreach (DB::table('oauth_clients')->orderBy('id')->pluck('id') as $uuid) {
            $counter++;
            $uuidToNewBigint[(string) $uuid] = $counter;
            DB::table('oauth_clients')->where('id', $uuid)->update(['id_old' => $counter]);
        }

        if (! Schema::hasColumn('oauth_access_tokens', 'client_id_old')) {
            Schema::table('oauth_access_tokens', function (Blueprint $table) {
                $table->unsignedBigInteger('client_id_old')->nullable()->after('client_id');
            });
        }
        if (! Schema::hasColumn('oauth_auth_codes', 'client_id_old')) {
            Schema::table('oauth_auth_codes', function (Blueprint $table) {
                $table->unsignedBigInteger('client_id_old')->nullable()->after('client_id');
            });
        }
        if (! Schema::hasColumn('oauth_personal_access_clients', 'client_id_old')) {
            Schema::table('oauth_personal_access_clients', function (Blueprint $table) {
                $table->unsignedBigInteger('client_id_old')->nullable()->after('client_id');
            });
        }

        // Re-link dependent rows. Any orphaned rows whose client_id has
        // no matching UUID client are deleted so the final NOT NULL
        // swap does not trip.
        DB::table('oauth_access_tokens')
            ->whereNotIn('client_id', fn ($q) => $q->select('id')->from('oauth_clients'))
            ->delete();
        DB::table('oauth_auth_codes')
            ->whereNotIn('client_id', fn ($q) => $q->select('id')->from('oauth_clients'))
            ->delete();
        DB::table('oauth_personal_access_clients')
            ->whereNotIn('client_id', fn ($q) => $q->select('id')->from('oauth_clients'))
            ->delete();

        foreach ($uuidToNewBigint as $uuid => $newBigint) {
            DB::table('oauth_access_tokens')->where('client_id', $uuid)->update(['client_id_old' => $newBigint]);
            DB::table('oauth_auth_codes')->where('client_id', $uuid)->update(['client_id_old' => $newBigint]);
            DB::table('oauth_personal_access_clients')->where('client_id', $uuid)->update(['client_id_old' => $newBigint]);
        }

        Schema::table('oauth_clients', function (Blueprint $table) {
            $table->dropPrimary('primary');
            $table->dropColumn('id');
        });
        DB::statement("ALTER TABLE {$prefix}oauth_clients CHANGE id_old id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (id)");

        Schema::table('oauth_access_tokens', function (Blueprint $table) {
            $table->dropColumn('client_id');
        });
        DB::statement("ALTER TABLE {$prefix}oauth_access_tokens CHANGE client_id_old client_id BIGINT UNSIGNED NOT NULL");

        Schema::table('oauth_auth_codes', function (Blueprint $table) {
            $table->dropColumn('client_id');
        });
        DB::statement("ALTER TABLE {$prefix}oauth_auth_codes CHANGE client_id_old client_id BIGINT UNSIGNED NOT NULL");

        Schema::table('oauth_personal_access_clients', function (Blueprint $table) {
            $table->dropColumn('client_id');
        });
        DB::statement("ALTER TABLE {$prefix}oauth_personal_access_clients CHANGE client_id_old client_id BIGINT UNSIGNED NOT NULL");
    }
};
