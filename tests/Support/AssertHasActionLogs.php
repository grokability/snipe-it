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
        Assert::assertEquals($statuses, $item->assetlog()->orderBy('id')->pluck('action_type')->toArray(), "Failed asserting that action logs match");
    }

}