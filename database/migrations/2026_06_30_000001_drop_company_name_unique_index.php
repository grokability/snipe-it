<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Drops the DB-level unique constraint on `companies.name`. Uniqueness is
 * enforced at the application layer via the `unique_undeleted` validation
 * rule, which (unlike the DB index) correctly excludes soft-deleted rows —
 * so a company that was trashed can have its name reused without leaving a
 * stale schema constraint to trip over.
 *
 * Same pattern as the historical 2015_07_25_055415_remove_email_unique_constraint
 * migration that dropped the equivalent constraint on `users.email`. The
 * down() is intentionally a no-op because re-adding a unique index on a
 * table that may by then have legitimate duplicates would fail.
 *
 * Background: the asset importer was tripping this index when a
 * company *existed* but was hidden by CompanyableScope from the importer's
 * user — the lookup missed it, the code fell through to INSERT, and the
 * DB rejected the row. This migration removes the underlying foot-gun so the
 * same shape of bug can't bonk an import run again.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropUnique('companies_name_unique');
        });
    }

    public function down(): void
    {
        // Intentionally empty — see header comment.
    }
};
