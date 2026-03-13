<?php

return array(

    'deleted' => 'Obrisani model imovine',
    'does_not_exist' => 'Model ne postoji.',
    'no_association' => 'UPOZORENJE! Model za ovu stavku je ili pogrešan ili nedostaje!',
    'no_association_fix' => 'Ovo će polomiti stvari na čudne i užasne načine. Uredite odmah ovu imovinu da bi ste je povezali sa modelom.',
    'assoc_users'	 => 'Ovaj je model trenutno povezan s jednom ili više imovina i ne može se izbrisati. Izbrišite imovinu pa pokušajte ponovo. ',
    'invalid_category_type' => 'Ova kategorija mora biti kategorija imovine.',

    'create' => array(
        'error'   => 'Model nije kreiran, pokušajte ponovo.',
        'success' => 'Model je uspešno kreiran.',
        'duplicate_set' => 'Model imovine s tim nazivom, proizvođačem i brojem modela već postoji.',
    ),

    'update' => array(
        'error'   => 'Model nije ažuriran, pokušajte ponovo',
        'success' => 'Model je uspešno ažuriran.',
    ),

    'delete' => array(
        'confirm'   => 'Jeste li sigurni da želite izbrisati ovaj model imovine?',
        'error'   => 'Došlo je do problema s brisanjem modela. Molim pokušajte ponovo.',
        'success' => 'Model je uspešno izbrisan.'
    ),

    'restore' => array(
        'error'   		=> 'Model nije obnovljen, pokušajte ponovo',
        'success' 		=> 'Model je uspešno obnovljen.'
    ),

    'bulkedit' => array(
        'error'   		=> 'Polja nisu menjana, tako da ništa nije ažurirano.',
        'success' 		=> 'Model je uspešno izmenjen. |:model_count modela je uspešno izmenjeno.',
        'warn'          => 'Spremate se da izmenite svojstva sledećeg modela:|Spremate se da izmenite svojstva sledećih :model_count modela:',

    ),

    'bulkdelete' => array(
        'error'   		    => 'Nijedan model nije odabran, tako da ništa nije izbrisano.',
        'success' 		    => 'Model je obrisan!|:success_count modela je obrisano!',
        'success_partial' 	=> ':success_count model(s) were deleted, however :fail_count were unable to be deleted because they still have assets associated with them.'
    ),

);
