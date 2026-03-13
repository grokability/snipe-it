<?php

return array(

    'does_not_exist' => 'Tillbehöret [:id] finns inte.',
    'not_found' => 'Tillbehöret hittades inte.',
    'assoc_users'	 => 'Detta tillbehör har för närvarande :count objekt utcheckade till användare. Checka in tillbehöret och försök igen. ',

    'create' => array(
        'error'   => 'Tillbehöret kunde inte skapas. Vänligen försök igen.',
        'success' => 'Tillbehör skapat.'
    ),

    'update' => array(
        'error'   => 'Tillbehöret kunde inte uppdateras. Vänligen försök igen.',
        'success' => 'Tillbehör uppdaterat.'
    ),

    'delete' => array(
        'confirm'   => 'Är du säker på att du vill ta bort det här tillbehöret?',
        'error'   => 'Ett fel uppstod när tillbehöret skulle tas bort. Vänligen försök igen.',
        'success' => 'Tillbehör raderat.'
    ),

     'checkout' => array(
        'error'   		=> 'Tillbehöret kunde inte checkas ut. Vänligen försök igen.',
        'success' 		=> 'Tillbehör utcheckat.',
        'unavailable'   => 'Tillbehöret är inte tillgängligt för utcheckning. Kontrollera antal tillgängligt',
        'user_does_not_exist' => 'Användaren är ogiltig. Vänligen försök igen.',
         'checkout_qty' => array(
            'lte'  => 'Det finns för närvarande bara ett tillgängligt tillbehör av den här typen, och du försöker checka ut :checkout_qty stycken. Vänligen justera utcheckningsantalet eller det totala lagret av detta tillbehör och försök igen.|Det finns totalt :number_currently_remaining tillgängliga tillbehör, och du försöker checka ut :checkout_qty stycken. Vänligen justera utcheckningsantalet eller det totala lagret av detta tillbehör och försök igen.',
            ),
           
    ),

    'checkin' => array(
        'error'   		=> 'Tillbehöret kunde inte checkas in. Vänligen försök igen.',
        'success' 		=> 'Tillbehör incheckat.',
        'user_does_not_exist' => 'Användaren är ogiltig. Vänligen försök igen.'
    )


);
