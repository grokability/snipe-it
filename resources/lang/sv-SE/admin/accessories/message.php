<?php

return [

    'does_not_exist' => 'Tillbehöret [:id] finns inte.',
    'not_found' => 'Tillbehöret hittades inte.',
    'assoc_users' => 'Detta tillbehör har för närvarande :count objekt utcheckade till användare. Checka in tillbehöret och försök igen. ',

    'create' => [
        'error' => 'Tillbehöret kunde inte skapas. Vänligen försök igen.',
        'success' => 'Tillbehör skapat.',
    ],

    'update' => [
        'error' => 'Tillbehöret kunde inte uppdateras. Vänligen försök igen.',
        'success' => 'Tillbehör uppdaterat.',
    ],

    'delete' => [
        'confirm' => 'Är du säker på att du vill ta bort det här tillbehöret?',
        'error' => 'Ett fel uppstod när tillbehöret skulle tas bort. Vänligen försök igen.',
        'success' => 'Tillbehör raderat.',
        'bulk_success' => 'Tillbehör har raderats.|:count tillbehör har raderats.',
        'partial_success' => ':count tillbehör har raderats framgångsrikt, men andra kunde inte raderas. Se nedan för detaljer.|:count tillbehör har raderats framgångsrikt, men andra kunde inte raderas. Se nedan för detaljer.',
    ],

    'checkout' => [
        'error' => 'Tillbehöret kunde inte checkas ut. Vänligen försök igen.',
        'success' => 'Tillbehör utcheckat.',
        'unavailable' => 'Tillbehöret är inte tillgängligt för utcheckning. Kontrollera antal tillgängligt',
        'user_does_not_exist' => 'Användaren är ogiltig. Vänligen försök igen.',
        'checkout_qty' => [
            'lte' => 'Det finns för närvarande bara ett tillgängligt tillbehör av den här typen, och du försöker checka ut :checkout_qty stycken. Vänligen justera utcheckningsantalet eller det totala lagret av detta tillbehör och försök igen.|Det finns totalt :number_currently_remaining tillgängliga tillbehör, och du försöker checka ut :checkout_qty stycken. Vänligen justera utcheckningsantalet eller det totala lagret av detta tillbehör och försök igen.',
        ],

    ],

    'checkin' => [
        'error' => 'Tillbehöret kunde inte checkas in. Vänligen försök igen.',
        'success' => 'Tillbehör incheckat.',
        'user_does_not_exist' => 'Användaren är ogiltig. Vänligen försök igen.',
    ],

    'restore' => [
        'error' => 'Accessory was not restored, please try again',
        'success' => 'Accessory restored successfully.',
    ],

    'bulk' => [
        'no_accessories_selected' => 'No accessories were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Inga fält ändrades, så ingenting uppdaterades.',
        'skipped_unauthorized' => ':count accessory(s) were skipped because you do not have permission to update them.',

        'update' => [
            'success' => ':count accessory was updated.|:count accessories were updated.',
            'none_updated' => 'The selected accessory could not be updated. See errors below for details.|None of the selected accessories could be updated. See errors below for details.',
        ],

        'restore' => [
            'success' => ':count accessory was restored.|:count accessories were restored.',
        ],

        'checkin_selected' => [
            'no_rows_selected' => 'No checked-out accessories were selected.',
            'success' => ':count accessory checkout was returned.|:count accessory checkouts were returned.',
        ],
    ],
];
