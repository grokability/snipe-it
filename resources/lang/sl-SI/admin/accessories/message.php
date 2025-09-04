<?php

return array(

    'does_not_exist' => 'Dodatek [:id] ne obstaja.',
    'not_found' => 'That accessory was not found.',
    'assoc_users'	 => 'Ta dodatek trenutno vsebuje: štetje predmetov, elementov ki so izdani uporabnikom. Preverite dodatke in poskusite znova. ',

    'create' => array(
        'error'   => 'Dodatek ni bila ustvarjen, poskusite znova.',
        'success' => 'Dodatek je bil uspešno ustvarjen.'
    ),

    'update' => array(
        'error'   => 'Dodatek ni bil posodobljen, poskusite znova',
        'success' => 'Dodatek je bil uspešno posodobljen.'
    ),

    'delete' => array(
        'confirm'   => 'Ali ste prepričani, da želite izbrisati ta dodatek?',
        'error'   => 'Prišlo je do napake pri brisanju dodatka. Prosim poskusite ponovno.',
        'success' => 'Dodatek je bil uspešno izbrisan.'
    ),

     'checkout' => array(
        'error'   		=> 'Dodatek ni bil izdan, poskusite znova',
        'success' 		=> 'Dodatek uspešno izdan.',
        'unavailable'   => 'Dodatek ni na voljo za plačilo. Preverite razpoložljivo količino',
        'user_does_not_exist' => 'Uporabnik je napačen. Prosim poskusite ponovno.',
         'checkout_qty' => array(
            'lte'  => 'Trenutno je na voljo samo en dodatek te vrste, vi pa poskušate kupiti :checkout_qty. Prilagodite količino za prevzem ali skupno zalogo tega dodatka in poskusite znova.|Na voljo je :number_currently_remaining skupno dodatne opreme, vi pa poskušate kupiti :checkout_qty. Prilagodite količino za prevzem ali skupno zalogo tega dodatka in poskusite znova.',
            ),
           
    ),

    'checkin' => array(
        'error'   		=> 'Dodatek ni bil sprejet, poskusite znova',
        'success' 		=> 'Dodatek uspešno sprejet.',
        'user_does_not_exist' => 'Uporabnik ne obstaja. Prosim poskusite ponovno.'
    )


);
