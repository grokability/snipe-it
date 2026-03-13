<?php

return array(
    'about_licenses_title'      => 'Tietoja lisensseistä',
    'about_licenses'            => 'Ohjelmistojen seurantaan käytetään lisenssejä. Lisenssejä on rajattu määrä asennettavaksi',
    'checkin'  					=> 'Palauta lisenssi',
    'checkout_history'  		=> 'Luovutushistoria',
    'checkout'  				=> 'Luovuta lisenssi',
    'edit'  					=> 'Muokkaa lisenssiä',
    'filetype_info'				=> 'Sallitut tiedostotyypit ovat png, gif, jpg, jpeg, doc, docx, pdf, txt, zip ja rar.',
    'clone'  					=> 'Monista lisenssi',
    'history_for'  				=> 'Käyttöhistoria lisenssille ',
    'in_out'  					=> 'Toiminto',
    'info'  					=> 'Lisenssin lisätiedot',
    'license_seats'  			=> 'Lisenssien määrä',
    'seat'  					=> 'Määrä',
    'seat_count'  				=> 'Seat :count',
    'seats'  					=> 'Määrät',
    'software_licenses'  		=> 'Ohjelmistolisenssit',
    'user'  					=> 'Käyttäjä',
    'view'  					=> 'Näytä lisenssi',
    'delete_disabled'           => 'Tätä lisenssiä ei voi vielä poistaa, koska jotkin tarvikkeet ovat vielä luovutettuina.',
    'bulk'                      =>
        [
            'checkin_all'           => [
                'button'            => 'Palauta kaikki paikat',
                'modal'             => 'This action will checkin one seat. | This action will checkin all :checkedout_seats_count seats for this license.',
                'enabled_tooltip'   => 'Palauttaa KAIKKI paikat tälle lisenssille sekä käyttäjiltä että laitteilta',
                'disabled_tooltip'  => 'Tämä ei ole käytössä, koska paikkoja ei ole tällä hetkellä luovutettuina',
                'disabled_tooltip_reassignable'  => 'Tämä ei ole käytössä, koska lisenssi ei ole uudelleenosoitettavissa',
                'success'           => 'Lisenssi onnistuneesti palautettu! - Kaikki lisenssit palautettiin onnistuneesti!',
                'log_msg'           => 'Checked in via bulk license checkin in license GUI',
            ],

            'checkout_all'              => [
                'button'                => 'Luovuta kaikki paikat',
                'modal'                 => 'Tämä toiminto luovuttaa yhden paikan ensimmäiselle käytettävissä olevalle käyttäjälle. - Tämä toiminto luovuttaa kaikki :available_seats_count paikat ensimmäisille saatavilla oleville käyttäjille. Käyttäjää pidetään saatavilla tälle paikalle, jos hänellä ei ole jo tätä lisenssiä luovutettuna heille, ja lisenssien automaattinen luovuttaminen on käytössä heidän käyttäjätilillään.',
                'enabled_tooltip'   => 'Luovuta KAIKKI paikat (niin monta kuin on saatavilla) KAIKILLE käyttäjille',
                'disabled_tooltip'  => 'Tämä ei ole käytössä, sillä paikkoja ei ole saatavilla',
                'success'           => 'Lisenssi onnistuneesti luovutettu! - Kaikki lisenssit luovutettiin onnistuneesti!',
                'error_no_seats'    => 'Tälle lisenssille ei ole jäljellä jäljellä yhtään paikkaa.',
                'warn_not_enough_seats'    => ':count käyttäjälle määriteltiin tämää lisenssi, mutta saatavilla olevat lisenssipaikat loppuivat kesken.',
                'warn_no_avail_users'    => 'Ei tehtävää. Käyttäjiä joilla ei jo ole tätä lisenssiä ei ole.',
                'log_msg'           => 'Luovutettu lisenssien massalainauskäyttöliittymän kautta',


            ],
    ],

    'below_threshold' => 'Tälle lisenssille jää vain :remaining_count istuimet ja vähimmäismäärä :min_amt. Voit halutessasi harkita useampien paikkojen ostamista.',
    'below_threshold_short' => 'Tämä kohta on alle vaaditun vähimmäismäärän.',
);
