<?php

return array(

    'invalid_category_type' => 'Kategorin måste vara en förbrukningsvarukategori.',
    'does_not_exist' => 'Förbrukningvaran existerar inte.',

    'create' => array(
        'error'   => 'Förbrukningsvaran kunde inte skapas. Vänligen försök igen.',
        'success' => 'Förbrukningsvara skapad.'
    ),

    'update' => array(
        'error'   => 'Förbrukningsvaran kunde inte uppdateras.Vänligen försök igen.',
        'success' => 'Förbruksningsvara uppdaterad.'
    ),

    'delete' => array(
        'confirm'   => 'Är du säker på att du vill radera denna förbrukningsartiklar?',
        'error'   => 'Kunde inte ta bort förbrukningsvaran. Vänligen försök igen.',
        'success' => 'Förbrukningsvaran raderad.'
    ),

     'checkout' => array(
        'error'   		=> 'Förbrukningsvaran kunde inte checkas ut. Vänligen försök igen.',
        'success' 		=> 'Förbrukningsvaran utcheckad.',
        'user_does_not_exist' => 'Användaren är ogiltig. Vänligen försök igen.',
         'unavailable'      => 'Det finns inte tillräckligt med förbrukningsvaror för denna utcheckning. Vänligen kontrollera antalet kvar i lager. ',
    ),

    'checkin' => array(
        'error'   		=> 'Förbrukningsvaran kunde inte checkas in. Vänligen försök igen.',
        'success' 		=> 'Förbrukningsvara incheckad.',
        'user_does_not_exist' => 'Användaren är ogiltig. Vänligen försök igen.'
    )


);
