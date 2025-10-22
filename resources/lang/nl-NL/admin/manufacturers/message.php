<?php

return array(

    'support_url_help' => 'Variabelen <code>{LOCALE}</code>, <code>{SERIAL}</code>, <code>{MODEL_NUMBER}</code>, en <code>{MODEL_NAME}</code> kunnen worden gebruikt in je URL om deze waarden automatisch in te vullen bij het bekijken van assets - bijvoorbeeld https://checkcoverage. pple.com/{LOCALE}/{SERIAL}.',
    'does_not_exist' => 'Fabrikant bestaat niet.',
    'assoc_users'	 => 'Deze fabrikant is verbonden met 1 of meerdere modellen en kan hierdoor niet verwijderd worden. Controleer de modellen zodat er geen verbinding meer is met deze fabrikant en probeer opnieuw. ',

    'create' => array(
        'error'   => 'Fabrikant was niet aangemaakt, Probeer opnieuw.',
        'success' => 'Fabrikant aangemaakt.'
    ),

    'update' => array(
        'error'   => 'Fabrikant niet bijgewerkt, probeer opnieuw',
        'success' => 'Fabrikant succesvol bijgewerkt.'
    ),

    'restore' => array(
        'error'   => 'Fabrikant is niet hersteld, probeer het opnieuw',
        'success' => 'Fabrikant succesvol hersteld.'
    ),

    'delete' => array(
        'confirm' => 'Weet u zeker dat deze fabrikant verwijderd kan worden?',
        'error'   => 'Er was een probleem bij het verwijderen. Probeer opnieuw.',
        'success'                => 'Manufacturer deleted successfully.',
        'bulk_success'           => 'Manufacturers deleted successfully.',
        'partial_success'        => 'Manufacturer deleted successfully. See additional information below. | :count manufacturers were deleted successfully. See additional information below.',
    )

);
