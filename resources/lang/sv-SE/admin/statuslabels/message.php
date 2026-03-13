<?php

return [

    'does_not_exist' => 'Statusetiketten existerar inte.',
    'deleted_label' => 'Statusetikett borttagen',
    'assoc_assets'	 => 'Denna statusetikett är för närvarande associerad med minst en tillgång och kan inte raderas. Uppdatera dina tillgångar och försök igen. ',

    'create' => [
        'error'   => 'Statusetiketten skapades inte, försök igen.',
        'success' => 'Statusetikett skapad.',
    ],

    'update' => [
        'error'   => 'Statusetiketten uppdaterades inte, var god försök igen',
        'success' => 'Statusetikett uppdaterad.',
    ],

    'delete' => [
        'confirm'   => 'Är du säker på att du vill radera denna statusetikett?',
        'error'   => 'Det gick inte att ta bort statusetiketten. Var god försök igen.',
        'success' => 'Statusetiketten har tagits bort.',
    ],

    'help' => [
        'undeployable'   => 'Dessa tillgångar kan inte tilldelas någon.',
        'deployable'   => 'Dessa tillgångar kan checkas ut. När de har tilldelats, antar de en metastatus på <i class="fas fa-circle text-blue"></i> <strong>Levererad</strong>.',
        'archived'   => 'Dessa tillgångar kan inte checkas ut och visas enbart i den arkiverade vyn. Detta är användbart för att behålla information om tillgångar för budgetering/historiska ändamål men att hålla dem borta från den dagliga tillgångslistan.',
        'pending'   => 'Dessa tillgångar kan ännu inte tilldelas någon som ofta används för föremål som är ute för reparation, men förväntas återgå till omlopp.',
    ],

];
