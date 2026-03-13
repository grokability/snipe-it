<?php

return array(

    'does_not_exist' => 'Tarvik [:id] ei eksisteeri.',
    'not_found' => 'Seda tarvikut ei leitud.',
    'assoc_users'	 => 'Seda tarvikut on praegu :count väljastatud kasutajatele. Palun kontrollige tarvikuid ja proovige uuesti. ',

    'create' => array(
        'error'   => 'Tarvikut ei loodud, proovige uuesti.',
        'success' => 'See tarvik loodi edukalt.'
    ),

    'update' => array(
        'error'   => 'Tarvikut ei uuendatud. Proovige uuesti',
        'success' => 'Tarvikut uuendati edukalt.'
    ),

    'delete' => array(
        'confirm'   => 'Kas olete kindel, et soovite seda tarvikut kustutada?',
        'error'   => 'Tarvikut ei õnnestunud kustutada. Palun proovi uuesti.',
        'success' => 'Lisaseade kustutati edukalt.'
    ),

     'checkout' => array(
        'error'   		=> 'Lisatarvikut ei kontrollitud, palun proovige uuesti',
        'success' 		=> 'Lisaseade edukalt kontrollitud.',
        'unavailable'   => 'Tarvik ei ole väljastamiseks saadaval. Kontrolli laoseisu',
        'user_does_not_exist' => 'See kasutaja on kehtetu. Palun proovi uuesti.',
         'checkout_qty' => array(
            'lte'  => 'Praegu on saadaval ainult üks seda tüüpi lisaseade ja te üritate väljastada :checkout_qty. Palun kohandage väljastatavate lisaseadmete kogust või selle lisaseadme koguarvu ja proovige uuesti.Saadaval on :number_currently_remaining lisaseadet ja te üritate väljastada :checkout_qty. Palun kohandage väljastavate lisaseadmete kogust või selle lisaseadme koguarvu ja proovige uuesti.',
            ),
           
    ),

    'checkin' => array(
        'error'   		=> 'Lisatarvikut ei olnud märgitud, palun proovige uuesti',
        'success' 		=> 'Lisaseade kontrollitud edukalt.',
        'user_does_not_exist' => 'See kasutaja on kehtetu. Palun proovi uuesti.'
    )


);
