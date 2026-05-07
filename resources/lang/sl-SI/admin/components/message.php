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

    'checkin' => [
        'error' => 'Komponenta ni bila prevzeta, poskusite znova',
        'success' => 'Komponenta je bila uspešno prevzeta.',
        'user_does_not_exist' => 'Ta uporabnik ni veljaven. Prosim poskusite ponovno.',
    ],

];
