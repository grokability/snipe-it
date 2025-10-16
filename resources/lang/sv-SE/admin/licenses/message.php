<?php

return array(

    'does_not_exist' => 'Licensen finns inte eller så har du inte behörighet att se den.',
    'user_does_not_exist' => 'Användaren finns inte eller så har du inte behörighet att se den.',
    'asset_does_not_exist' 	=> 'Den tillgång du försöker sammankoppla med denna licens existerar inte.',
    'owner_doesnt_match_asset' => 'Den tillgång du försöker sammankoppla med denna licens ägs av någon annan än den person som valts i den tilldelade till rullgardinsmenyn.',
    'assoc_users'	 => 'Licensen är nu utcheckad till en användare och kan inte raderas. Var god kontrollera licensen först och försök sedan radera igen.',
    'select_asset_or_person' => 'Du måste välja en tillgång eller en användare, men inte båda.',
    'not_found' => 'Licensen hittades inte',
    'seats_available' => ':seat_count säten tillgängliga',


    'create' => array(
        'error'   => 'Licensen kunde inte skapas. Vänligen försök igen.',
        'success' => 'Licens skapad.'
    ),

    'deletefile' => array(
        'error'   => 'Filen har inte tagits bort. Vänligen försök igen.',
        'success' => 'Filen har tagits bort.',
    ),

    'upload' => array(
        'error'   => 'Fil(er) kunde inte laddas upp. Vänligen försök igen.',
        'success' => 'Fil(er) uppladdad(e).',
        'nofiles' => 'Du valde inte några filer för uppladdning eller så är filen du försöker ladda upp för stor',
        'invalidfiles' => 'En eller flera av dina filer är för stora eller är av en filtyp som inte är tillåten. Tillåtna filtyper är png, gif, jpg, jpeg, doc, docx, pdf, txt, zip, rar, rtf, xml och lic.',
    ),

    'update' => array(
        'error'   => 'Licensen kunde inte uppdateras. Vänligen försök igen.',
        'success' => 'Licens uppdaterad.'
    ),

    'delete' => array(
        'confirm'   => 'Är du säker på att du vill radera denna licens?',
        'error'   => 'Licensen kunde inte tas bort. Vänligen försök igen.',
        'success' => 'Licensen har tagits bort.'
    ),

    'checkout' => array(
        'error'   => 'Det gick inte att checka ut licensen. Vänligen försök igen.',
        'success' => 'Licens utcheckad.',
        'not_enough_seats' => 'Inte tillräckligt med licenssäten tillgängliga för utcheckning',
        'mismatch' => 'Licenssätet som anges matchar inte licensen',
        'unavailable' => 'Detta säte är inte tillgängligt för utcheckning.',
        'license_is_inactive' => 'This license is expired or terminated.',
    ),

    'checkin' => array(
        'error'   => 'Det gick inte att checka in licensen. Vänligen försök igen.',
        'not_reassignable' => 'Seat has been used',
        'success' => 'Licens incheckad.'
    ),

);
