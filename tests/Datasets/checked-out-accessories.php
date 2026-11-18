<?php

use App\Models\Accessory;

dataset('checked out accessories', [
    'checked out to user' => [fn () => Accessory::factory()->checkedOutToUser()->create()],
    'checked out to asset' => [fn () => Accessory::factory()->checkedOutToAsset()->create()],
    'checked out to location' => [fn () => Accessory::factory()->checkedOutToLocation()->create()],
]);
