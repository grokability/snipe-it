<?php

return [

    'does_not_exist' => 'Komponenten existerar inte.',

    'create' => [
        'error' => 'Komponenten kunde inte skapas. Vänligen försök igen.',
        'success' => 'Komponent skapad.',
    ],

    'update' => [
        'error' => 'Komponenten kunde inte uppdateras. Vänligen försök igen.',
        'success' => 'Komponent uppdaterad.',
    ],

    'delete' => [
        'confirm' => 'Är du säker på att du vill radera den här komponenten?',
        'error' => 'Kunde inte ta bort komponenten. Vänligen försök igen.',
        'success' => 'Komponent raderad.',
        'error_qty' => 'Some components of this type are still checked out. Please check them in and try again.',
    ],

    'checkout' => [
        'error' => 'Komponenten kunde inte checkas. Vänligen försök igen.',
        'success' => 'Komponent utcheckad.',
        'user_does_not_exist' => 'Användaren är ogiltig. Vänligen försök igen.',
        'unavailable' => 'Inte tillräckligt med komponenter kvar: :remaining kvar, :requested efterfrågat ',
    ],

    'checkin' => [
        'error' => 'Komponenten kunde inte checkas in. Vänligen försök igen.',
        'success' => 'Komponent incheckad.',
        'user_does_not_exist' => 'Den användaren är ogiltig. Vänligen försök igen.',
    ],

];
