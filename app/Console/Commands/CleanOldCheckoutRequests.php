<?php

namespace App\Console\Commands;

use App\Models\CheckoutRequest;
use Illuminate\Console\Command;

class CleanOldCheckoutRequests extends Command
{
    private int $deletions = 0;
    private int $skips = 0;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'snipeit:clean-old-checkout-requests';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Removes checkout requests that reference deleted assets or users.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $requests = CheckoutRequest::with([
            'user' => function ($query) {
                $query->withTrashed();
            },
            'requestedItem' => function ($query) {
                $query->withTrashed();
            },
        ])->get();

        $this->info("Processing {$requests->count()} checkout requests");

        $this->withProgressBar($requests, function ($request) {
            if ($this->shouldForceDelete($request)) {
                $request->forceDelete();
                $this->deletions++;
                return;
            }

            if ($this->shouldSoftDelete($request)) {
                $request->delete();
                $this->deletions++;
                return;
            }

            $this->skips++;
        });

        $this->info("Final deletion count: $this->deletions, and skip count: $this->skips");

        return 0;
    }

    private function shouldForceDelete(CheckoutRequest $request)
    {
        // check if the requestable or user relationship is null
        return !$request->requestable || !$request->user;
    }

    private function shouldSoftDelete(CheckoutRequest $request)
    {
        return $request->requestable->trashed() || $request->user->trashed();
    }
}
