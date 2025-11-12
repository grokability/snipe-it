<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {

        /**
         * We actually *do* have an index on action_type, so this query shouldn't take too terribly long. I don't know
         * that we have a ton of people canceling requests (and only certain types of request-cancellation use this
         * erroneous action_type), so I feel pretty comfortable with this fixup. Fingers crossed!
         * */

        DB::table('action_logs')->where('action_type', 'request_canceled')->update(['action_type' => 'request canceled']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // no down migration for this one
    }
};
