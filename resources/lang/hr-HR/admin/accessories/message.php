<?php

return array(

    'does_not_exist' => 'Dodatna oprema [:id] ne postoji.',
    'not_found' => 'Ova dodatna oprema nije pronađena.',
    'assoc_users'	 => 'Ovaj pribor trenutačno ima: brojčanu stavku označenu korisnicima. Provjerite pribor i pokušajte ponovo.',

    'create' => array(
        'error'   => 'Dodatak nije izrađen, pokušajte ponovo.',
        'success' => 'Dodatak je uspješno izrađen.'
    ),

    'update' => array(
        'error'   => 'Dodatak nije ažuriran, pokušajte ponovo',
        'success' => 'Dodatak je uspješno ažuriran.'
    ),

    'delete' => array(
        'confirm'   => 'Jeste li sigurni da želite izbrisati ovaj dodatak?',
        'error'   => 'Došlo je do problema s brisanjem dodatne opreme. Molim te pokušaj ponovno.',
        'success' => 'Dodatak je uspješno izbrisan.'
    ),

     'checkout' => array(
        'error'   		=> 'Dodatak nije provjeren, pokušajte ponovo',
        'success' 		=> 'Usluga je uspješno provjerena.',
        'unavailable'   => 'Dodatna oprema nije dostupna za izdavanje. Provjerite dostupnu količinu',
        'user_does_not_exist' => 'Taj je korisnik nevažeći. Molim te pokušaj ponovno.',
         'checkout_qty' => array(
            'lte'  => 'Trenutno je dostupan samo jedan primjerak dodatne opreme ovog tipa, a Vi pokušavate izdati :checkout_qty. Molimo korigujte broj izdavanja ili ukupni lager ove dodatne opreme i pokušajte ponovno.|Postoji :number_currently_remaining ukupno primjeraka dodatne opreme, a Vi pokušavate izdati :checkout_qty. Molimo korigujte broj izdavanja ili ukupni lager ove dodatne opreme i pokušajte ponovno.',
            ),
           
    ),

    'checkin' => array(
        'error'   		=> 'Dodatna oprema nije prijavljena, pokušajte ponovo',
        'success' 		=> 'Pristup je uspješno prijavljen.',
        'user_does_not_exist' => 'Taj je korisnik nevažeći. Molim te pokušaj ponovno.'
    )


);
