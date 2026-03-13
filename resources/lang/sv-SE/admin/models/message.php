<?php

return array(

    'deleted' => 'Raderad tillgångsmodell',
    'does_not_exist' => 'Modellen finns inte.',
    'no_association' => 'VARNING! Tillgångsmodellen för detta objekt är ogiltig eller saknas!',
    'no_association_fix' => 'Detta kommer att förstöra saker på märkliga sätt. Redigera denna tillgång nu för att tilldela det till en modell.',
    'assoc_users'	 => 'Denna modell är redan associerad med en eller flera tillgångar och kan inte tas bort. Ta bort tillgången och försök sedan igen. ',
    'invalid_category_type' => 'Denna kategori måste vara en tillgångskategori.',

    'create' => array(
        'error'   => 'Modellen skapades inte, försök igen.',
        'success' => 'Modellen skapad.',
        'duplicate_set' => 'En tillgångsmodell med det namnet, tillverkaren och modellnumret finns redan.',
    ),

    'update' => array(
        'error'   => 'Modellen uppdaterades inte, försök igen',
        'success' => 'Modellen uppdaterades.',
    ),

    'delete' => array(
        'confirm'   => 'Är du säker på att du vill ta bort denna modell?',
        'error'   => 'Kunde inte ta bort modellen. Försök igen.',
        'success' => 'Modellen borttagen.'
    ),

    'restore' => array(
        'error'   		=> 'Modellen kunde inte återskapas, försök igen',
        'success' 		=> 'Modellen återskapades.'
    ),

    'bulkedit' => array(
        'error'   		=> 'Inga fält ändrades, så ingenting uppdaterades.',
        'success' 		=> 'Modellen har uppdaterats. |:model_count modeller har uppdaterats.',
        'warn'          => 'Du är på väg att uppdatera egenskaperna för följande modell:|Du håller på att redigera egenskaperna för följande :model_count modeller:',

    ),

    'bulkdelete' => array(
        'error'   		    => 'Inga tillgångar valdes, så ingenting togs bort.',
        'success' 		    => 'Modell borttagen! |:success_count modeller borttagna!',
        'success_partial' 	=> ':success_count modell(erna) raderades, men :fail_count kunde inte raderas eftersom de fortfarande har tillgångar kopplade till sig.'
    ),

);
