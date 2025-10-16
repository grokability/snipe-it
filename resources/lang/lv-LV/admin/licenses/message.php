<?php

return array(

    'does_not_exist' => 'License does not exist or you do not have permission to view it.',
    'user_does_not_exist' => 'User does not exist or you do not have permission to view them.',
    'asset_does_not_exist' 	=> 'Aktīvs, kuru jūs mēģināt saistīt ar šo licenci, nepastāv.',
    'owner_doesnt_match_asset' => 'Aktīvs, kuru jūs mēģināt saistīt ar šo licenci, ir īpašumā somene, kas nav persona, kas atlasīta nolaižamajā sarakstā piešķirtajam.',
    'assoc_users'	 => 'Šobrīd šī licence tiek izrakstīta lietotājam un to nevar izdzēst. Vispirms pārbaudiet licenci un pēc tam mēģiniet dzēst vēlreiz.',
    'select_asset_or_person' => 'Jums jāizvēlas aktīvs vai lietotājs, bet ne abi.',
    'not_found' => 'Licence nav atrasta',
    'seats_available' => ':seat_count seats available',


    'create' => array(
        'error'   => 'Licence netika izveidota, lūdzu, mēģiniet vēlreiz.',
        'success' => 'Licence tika veiksmīgi izveidota.'
    ),

    'deletefile' => array(
        'error'   => 'Fails nav izdzēsts. Lūdzu mēģiniet vēlreiz.',
        'success' => 'Fails veiksmīgi izdzēsts.',
    ),

    'upload' => array(
        'error'   => 'Faili nav augšupielādēti. Lūdzu mēģiniet vēlreiz.',
        'success' => 'Faili (-i) ir veiksmīgi augšupielādēti.',
        'nofiles' => 'Jūs neesat atlasījis augšupielādējamos failus, vai arī fails, kuru mēģināt augšupielādēt, ir pārāk liels',
        'invalidfiles' => 'Viens vai vairāki jūsu faili ir pārāk lieli vai nav atļauto faila tipu. Atļautie failu tipi ir png, gif, jpg, jpeg, doc, docx, pdf, txt, zip, rar, rtf, xml un lic.',
    ),

    'update' => array(
        'error'   => 'Licence netika atjaunināta, lūdzu, mēģiniet vēlreiz',
        'success' => 'Licence tika veiksmīgi atjaunināta.'
    ),

    'delete' => array(
        'confirm'   => 'Vai tiešām vēlaties dzēst šo licenci?',
        'error'   => 'Radās problēma, dzēšot licenci. Lūdzu mēģiniet vēlreiz.',
        'success' => 'Licence tika veiksmīgi dzēsta.'
    ),

    'checkout' => array(
        'error'   => 'Pārbaudot licenci, radās problēma. Lūdzu mēģiniet vēlreiz.',
        'success' => 'Licence tika veiksmīgi pārbaudīta',
        'not_enough_seats' => 'Not enough license seats available for checkout',
        'mismatch' => 'The license seat provided does not match the license',
        'unavailable' => 'This seat is not available for checkout.',
        'license_is_inactive' => 'This license is expired or terminated.',
    ),

    'checkin' => array(
        'error'   => 'Licencē tika pārbaudīta problēma. Lūdzu mēģiniet vēlreiz.',
        'not_reassignable' => 'Seat has been used',
        'success' => 'Licence tika veiksmīgi reģistrēta'
    ),

);
