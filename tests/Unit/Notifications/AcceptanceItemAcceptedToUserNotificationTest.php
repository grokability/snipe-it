<?php

use App\Notifications\AcceptanceItemAcceptedToUserNotification;

test('constructor does not reparse pre formatted accepted date', function () {
    // GH #19511: the controller passes accepted_date already
    // formatted in the tenant's locale (e.g. `dd/mm/rrrr` renders
    // as `27/08/2026 10:00`). The notification constructor used to
    // run that pre-formatted string through Helper::getFormattedDateObject
    // a second time, which tried to `new Carbon('27/08/2026 10:00')`
    // and blew up on any locale format Carbon does not natively
    // parse (dd/mm/yyyy reads as m/d/y, month 27 fails). The
    // constructor must pass the value through unchanged.
    $polishFormatted = '27/08/2026 10:00';

    $notification = new AcceptanceItemAcceptedToUserNotification([
        'item_tag' => 'AT-1',
        'item_name' => 'Test',
        'item_model' => 'Model',
        'item_serial' => 'SN',
        'item_status' => 'Deployed',
        'accepted_date' => $polishFormatted,
        'assigned_to' => 'Test User',
        'company_name' => 'Test Co',
        'qty' => 1,
    ]);

    expect($notification->accepted_date)->toBe($polishFormatted);
});
