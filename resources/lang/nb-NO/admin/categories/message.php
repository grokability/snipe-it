<?php

return array(

    'does_not_exist' => 'Kategorien eksisterer ikke.',
    'assoc_models'	 => 'Denne kategorien er koblet til minst èn modell og kan ikke slettes. Vennligst oppdater dine modeller til å ikke referere til denne kategorien og prøv igjen. ',
    'assoc_items'	 => 'Denne kategorien er knyttet til minst én :asset_type og kan ikke slettes. Oppdater din :asset_type til å ikke lenger refererer til denne kategorien, og prøv på nytt. ',

    'create' => array(
        'error'   => 'Kategorien ble ikke opprettet, vennligst prøv igjen.',
        'success' => 'Kategorien ble opprettet.'
    ),

    'update' => array(
        'error'   => 'Kategorien ble ikke opprettet, vennligst prøv igjen',
        'success' => 'Kategorien ble oppdatert.',
        'cannot_change_category_type'   => 'Du kan ikke endre kategori typen når den har blitt opprettet',
    ),

    'delete' => array(
        'confirm'                => 'Er du sikker på du vil slette denne kategorien?',
        'error'                  => 'Det oppsto et problem ved sletting av kategorien. Vennligst prøv igjen.
',
        'success'                => 'Kategorien er slettet.',
        'bulk_success'           => 'Kategoriene er slettet.',
        'partial_success'        => 'Category deleted successfully. See additional information below. | :count categories were deleted successfully. See additional information below.',
    )

);
