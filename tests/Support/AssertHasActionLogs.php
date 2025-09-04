<?php

namespace Tests\Support;

use App\Models\Actionlog;
use Illuminate\Database\Eloquent\Model;
use PHPUnit\Framework\Assert;
use function PHPUnit\Framework\assertEquals;

trait AssertHasActionLogs
{
    public function assertHasTheseActionLogs(Model $item, array $statuses)
    {
        //note we have to do a 'reorder()' here because there is an implicit "order_by created_at" baked in to the relationship
        Assert::assertEquals($statuses, $item->assetlog()->reorder('id')->pluck('action_type')->toArray(), "Failed asserting that action logs match");
    }

}