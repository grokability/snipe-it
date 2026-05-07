<?php

namespace Tests\Support;

use Illuminate\Database\Eloquent\Model;
use PHPUnit\Framework\Assert;

trait AssertHasActionLogs
{
    public function assertHasTheseActionLogs(Model $item, array $statuses)
    {
        // note we have to do a 'reorder()' here because there is an implicit "order_by created_at" baked in to the relationship
        Assert::assertEquals($statuses, $item->assetlog()->reorder('id')->pluck('action_type')->toArray(), 'Failed asserting that action logs match');
    }
}
