<?php

return array(

    'does_not_exist' => 'Diese Kategorie existiert nicht.',
    'assoc_models'	 => 'Diese Kategorie kann nicht gelöscht werden, da sie bereits einem Modell zugewiesen ist. Bitte entfernen Sie diese Kategorie von Ihren Modellen und versuchen Sie es erneut. ',
    'assoc_items'	 => 'Diese Kategorie kann nicht gelöscht werden da sie bereits mit einem :asset_type verbunden ist. Bitte trennen Sie diese Kategorie von Ihrem :asset_type und versuchen Sie es erneut. ',

    'create' => array(
        'error'   => 'Die Kategorie konnte nicht erstellt werden, bitte versuchen Sie es erneut.',
        'success' => 'Die Kategorie wurde erfolgreich erstellt.'
    ),

    'update' => array(
        'error'   => 'Die Kategorie konnte nicht aktualisiert werden, bitte versuchen Sie es erneut',
        'success' => 'Die Kategorie wurde erfolgreich aktualisiert.',
        'cannot_change_category_type'   => 'Sie können den Kategorietyp nicht ändern, nachdem er erstellt wurde',
    ),

    'delete' => array(
        'confirm'                => 'Sind Sie sicher, dass Sie diese Kategorie löschen wollen?',
        'error'                  => 'Beim Löschen der Kategorie ist ein Problem aufgetreten. Bitte versuchen Sie es erneut.',
        'success'                => 'Kategorie wurde erfolgreich gelöscht.',
        'bulk_success'           => 'Kategorien wurden erfolgreich gelöscht.',
        'partial_success'        => 'Kategorie wurde erfolgreich gelöscht. Siehe weitere Informationen unten. | :count Kategorien wurden erfolgreich gelöscht. Siehe weitere Informationen unten.',
    )

);
