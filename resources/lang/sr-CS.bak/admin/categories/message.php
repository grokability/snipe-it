<?php

return array(

    'does_not_exist' => 'Kategorija ne postoji.',
    'assoc_models'	 => 'Ova je kategorija trenutno povezana sa barem jednim modelom i ne može se izbrisati. Ažurirajte svoje modele da se više ne referenciraju na ovu kategoriju i pokušajte ponovno. ',
    'assoc_items'	 => 'Ova je kategorija trenutno povezana s najmanje jednim: asset_type i ne može se izbrisati. Ažurirajte svoj: asset_type da se više ne referencira na ovu kategoriju i pokušajte ponovo. ',

    'create' => array(
        'error'   => 'Kategorija nije kreirana, pokušajte ponovo.',
        'success' => 'Kategorija je uspješno kreirana.'
    ),

    'update' => array(
        'error'   => 'Kategorija nije ažurirana, pokušajte ponovo',
        'success' => 'Kategorija je uspješno ažurirana.',
        'cannot_change_category_type'   => 'Kada je kreiran, tip kategorije nije moguće promeniti',
    ),

    'delete' => array(
        'confirm'   => 'Da li ste sigurni da želite izbrisati ovu kategoriju?',
        'error'   => 'Došlo je do problema s brisanjem kategorije. Molim pokušaj te ponovo.',
        'success' => 'Kategorija je uspešno izbrisana.'
    )

);
