<?php

return array(

    'does_not_exist' => 'Licence neexistuje nebo nemáte oprávnění k jejímu zobrazení.',
    'user_does_not_exist' => 'Uživatel neexistuje nebo nemáte oprávnění k jeho zobrazení.',
    'asset_does_not_exist' 	=> 'Majetek, který se pokoušíte spojit s touto licencí, neexistuje.',
    'owner_doesnt_match_asset' => 'Majetek, který se pokoušíte spojit s touto licencí, vlastní někdo jiný než osoba vybraná v rozbalovací nabídce k této licenci.',
    'assoc_users'	 => 'Licence je předána svému uživateli a nelze ji odstranit. Před odstraněním ji nejprve převezměte. ',
    'select_asset_or_person' => 'Musíte vybrat aktivum nebo uživatele, ale ne obojí.',
    'not_found' => 'Licence nenalezena',
    'seats_available' => ':seat_count míst k dispozici',


    'create' => array(
        'error'   => 'Licence nebyla vytvořena, zkuste to prosím znovu.',
        'success' => 'Licence byla úspěšně vytvořena.'
    ),

    'deletefile' => array(
        'error'   => 'Soubor se nepodařilo smazat. Prosím zkuste to znovu.',
        'success' => 'Soubor byl úspěšně smazán.',
    ),

    'upload' => array(
        'error'   => 'Soubor(y) se nepodařilo nahrát. Prosím zkuste to znovu.',
        'success' => 'Soubor(y) byly v pořádku nahrány.',
        'nofiles' => 'K nahrání jste nevybrali žádný, nebo příliš velký soubor',
        'invalidfiles' => 'Jeden nebo více označených souborů je příliš velkých nebo nejsou podporované. Povolenými příponami jsou png, gif, jpg, jpeg, doc, docx, pdf, txt, zip, rar, rtf, xml, a lic.',
    ),

    'update' => array(
        'error'   => 'Licence nebyla aktualizována, zkuste to prosím znovu',
        'success' => 'Licence byla úspěšně aktualizována.'
    ),

    'delete' => array(
        'confirm'   => 'Jste si jisti, že chcete odstranit tuto licenci?',
        'error'   => 'Vyskytl se problém při mazání licence. Zkuste to znovu prosím.',
        'success' => 'Licence byla úspěšně smazána.'
    ),

    'checkout' => array(
        'error'   => 'Vyskytl se problém při výdeji licence. Zkuste to znovu prosím.',
        'success' => 'Licence byla úspěšně vydána',
        'not_enough_seats' => 'Není k dispozici dostatek licenčních míst pro pokladnu',
        'mismatch' => 'Poskytnutá licence se neshoduje s licencí',
        'unavailable' => 'Tuto licenci nelze aktuálně přidělit.',
        'license_is_inactive' => 'This license is expired or terminated.',
    ),

    'checkin' => array(
        'error'   => 'Vyskytl se problém při ověřování licence. Zkuste to znovu prosím.',
        'not_reassignable' => 'Seat has been used',
        'success' => 'Licence byla úspěšně zkontrolována'
    ),

);
