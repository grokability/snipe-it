<?php

return [

    'does_not_exist' => 'Komponenta ne obstaja.',

    'create' => [
        'error' => 'Komponenta ni bila ustvarjena, poskusite znova.',
        'success' => 'Komponenta je bila uspešno ustvarjena.',
    ],

    'update' => [
        'error' => 'Komponenta ni bila posodobljena, poskusite znova',
        'success' => 'Komponenta je bila uspešno posodobljena.',
    ],

    'restore' => [
        'error' => 'Component was not restored, please try again',
        'success' => 'Component restored successfully.',
    ],

    'delete' => [
        'confirm' => 'Ali ste prepričani, da želite izbrisati to komponento?',
        'error' => 'Prišlo je do težave pri brisanju komponente. Prosim poskusite ponovno.',
        'success' => 'Komponenta je bila uspešno izbrisana.',
        'error_qty' => 'Some components of this type are still checked out. Please check them in and try again.',
    ],

    'checkout' => [
        'error' => 'Komponenta ni bila izdana, poskusite znova',
        'success' => 'Komponenta je bila uspešno izdana.',
        'user_does_not_exist' => 'Ta uporabnik ni veljaven. Prosim poskusite ponovno.',
        'unavailable' => 'Ni dovolj preostalih komponent: :remaining preostalo, :requested zahtevano ',
    ],

    'bulk' => [
        'no_components_selected' => 'No components were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Polja niso bila spremenjena, nič ni posodobljeno.',
        'skipped_unauthorized' => ':count component(s) were skipped because you do not have permission to update them.',

        'update' => [
            'success' => ':count component was updated.|:count components were updated.',
            'none_updated' => 'The selected component could not be updated. See errors below for details.|None of the selected components could be updated. See errors below for details.',
        ],

        'delete' => [
            'success' => ':count component was deleted.|:count components were deleted.',
            'partial_success' => ':count component was deleted. See errors below for the rest.|:count components were deleted. See errors below for the rest.',
        ],

        'restore' => [
            'success' => ':count component was restored.|:count components were restored.',
        ],

        'checkin_selected' => [
            'no_rows_selected' => 'No checked-out components were selected.',
            'success' => ':unit_count unit returned across :count checkout.|:unit_count units returned across :count checkouts.',
        ],
    ],

    'checkin' => [
        'error' => 'Komponenta ni bila prevzeta, poskusite znova',
        'success' => 'Komponenta je bila uspešno prevzeta.',
        'user_does_not_exist' => 'Ta uporabnik ni veljaven. Prosim poskusite ponovno.',
    ],

];
