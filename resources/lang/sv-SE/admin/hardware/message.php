<?php

return [

    'undeployable' => 'Följande tillgångar kan inte deployas och har tagits bort från utcheckningen: :asset_tags',
    'does_not_exist' => 'Tillgång existerar inte.',
    'does_not_exist_var' => 'Tillgång med taggen :asset_tag hittades inte.',
    'no_tag' => 'Ingen tillgångstagg angiven.',
    'does_not_exist_or_not_requestable' => 'Den tillgången finns inte eller är inte tillgänglig.',
    'assoc_users' => 'Denna tillgång har checkats ut till en användare och kan inte raderas. Kontrollera tillgången först och försök sedan radera igen. ',
    'warning_audit_date_mismatch' => 'Nästa inventeringsdatum för denna tillgång (:next_audit_date) är före det senaste inventeringsdatumet (:last_audit_date). Vänligen uppdatera nästa inventeringsdatum.',
    'labels_generated' => 'Etiketter har genererats.',
    'error_generating_labels' => 'Ett fel uppstod vid generering av etiketter.',
    'no_assets_selected' => 'Inga tillgångar valda.',

    'create' => [
        'error' => 'Tillgången skapades inte :( Försök igen.',
        'success' => 'Tillgången skapades.',
        'success_no_checkout' => 'Tillgången har skapats men checkades inte ut eftersom du saknar behörighet att checka ut tillgångar.',
        'checkout_skipped_no_permission' => 'Tillgången har skapats men checkades inte ut till den begärda mottagaren eftersom du saknar behörighet att checka ut tillgångar.',
        'success_linked' => 'Tillgången med taggen :tag har skapats. <strong><a href=":link" style="color: white;">Klicka här för att visa</a></strong>.',
        'multi_success_linked' => 'Tillgång med taggen :links skapades.|:count tillgångar skapades. :links.',
        'partial_failure' => 'En tillgång kunde inte skapas. Anledning: :failures|:count tillgångar kunde inte skapas. Anledning: :failures',
        'target_not_found' => [
            'user' => 'Den tilldelade användaren kunde inte hittas.',
            'asset' => 'Den tilldelade tillgången kunde inte hittas.',
            'location' => 'Den tilldelade platsen kunde inte hittas.',
        ],
    ],

    'update' => [
        'error' => 'Tillgången kunde inte uppdateras, försök igen',
        'success' => 'Tillgång uppdaterad.',
        'encrypted_warning' => 'Tillgången uppdaterades, men krypterade egenanpassade fält kunde inte uppdateras p.g.a. behörigheter',
        'nothing_updated' => 'Inga fält valdes. Ingenting uppdaterades.',
        'no_assets_selected' => 'Inga tillgångar valdes. Ingenting uppdaterades.',
        'assets_do_not_exist_or_are_invalid' => 'Valda tillgångar kan inte uppdateras.',
    ],

    'bulk_update' => [
        'success' => 'Tillgången har uppdaterats.|:count tillgångar har uppdaterats.',
        'partial' => ':success tillgång(ar) uppdaterades, :failed misslyckades. Se results-arrayen för detaljer.',
        'error' => 'Inga tillgångar uppdaterades. Se results-arrayen för detaljer.',
    ],

    'restore' => [
        'error' => 'Tillgången återställdes inte, försök igen',
        'success' => 'Tillgång återställd.',
        'bulk_success' => 'Återställning av tillgången lyckades.',
        'nothing_updated' => 'Inga tillgångar valda. Ingenting återställdes.',
    ],

    'audit' => [
        'error' => 'Tillgångsinventeringen misslyckades: :error ',
        'success' => 'Inventeringen av tillgången har loggats.',
    ],

    'deletefile' => [
        'error' => 'Filen kunde inte tas bort. Var god försök igen.',
        'success' => 'Filen har tagits bort.',
    ],

    'upload' => [
        'error' => 'Fil(er) kunde inte laddas upp. Var god försök igen.',
        'success' => 'Fil(er) har laddats upp.',
        'nofiles' => 'Du valde inte några filer för uppladdning, eller så är filen du försöker ladda upp för stor',
        'invalidfiles' => 'En eller fler av dina filer är för stora eller är en filtyp som inte är tillåten. Tillåtna filtyper är png, gif, jpg, doc, docx, pdf och txt.',
    ],

    'import' => [
        'import_button' => 'Bearbeta import',
        'error' => 'Vissa objekt importerades inte korrekt.',
        'errorDetail' => 'Följande objekt importerades inte på grund av fel.',
        'success' => 'Din fil har importerats',
        'file_delete_success' => 'Din fil har tagits bort',
        'file_delete_error' => 'Filen kunde inte raderas',
        'file_missing' => 'Den valda filen saknas',
        'file_already_deleted' => 'Den valda filen har redan tagits bort',
        'file_missing_on_disk' => 'Filen för denna import finns inte längre på disken. Den kan ha raderats utanför Snipe-IT. Radera detta inlägg och ladda upp filen igen för att försöka på nytt.',
        'file_empty' => 'Denna fil har inga datarader. Ingenting kan importeras från den.',
        'already_processing' => 'Denna import bearbetas för närvarande av en annan användare. Vänta tills den är klar innan du försöker igen.',
        'header_row_missing' => 'Denna fil har ingenigenkänd rubrikrad. Radera detta inlägg och ladda upp filen igen för att försöka på nytt.',
        'header_row_has_malformed_characters' => 'Ett eller flera attribut i rubrikraden innehåller felaktigt formatterade UTF-8-tecken',
        'content_row_has_malformed_characters' => 'Ett eller flera attribut i den första raden av innehållet innehåller felaktigt formatterade UTF-8-tecken',
        'transliterate_failure' => 'Translitterering från :encoding till UTF-8 misslyckades på grund av ogiltiga tecken i inmatningen',
        'bulk_delete' => [
            'button' => 'Radera valda (:count)',
            'confirm_title' => 'Radera valda importfiler?',
            'confirm_body' => 'Du är på väg att permanent radera :count importfil(er). Detta kan inte ångras.',
            'confirm_button' => 'Radera',
            'success' => 'Importfilen har raderats.|:count importfiler har raderats.',
            'skipped' => ':count fil(er) hoppades över eftersom du inte har behörighet att radera dem.',
            'select_all' => 'Markera alla filer på denna sida',
            'select_row' => 'Markera :file för massradering',
        ],
        'row_count' => '{0} Inga datarader i denna fil|{1} :count datarad att importera|[2,*] :count datarader att importera',
        'summary' => [
            'created' => 'Skapad',
            'updated' => 'Uppdaterad',
            'skipped' => 'Hoppades över som duplicerad',
            'errored' => 'Felaktig',
            'no_changes' => 'Importen avslutades men inget skapades eller uppdaterades. Varje rad hoppades över, vanligtvis eftersom underliggande poster redan fanns. Kontrollera antalen nedan och justera CSV-filen eller importtypen om det inte var vad du förväntade dig.',
        ],
        'type_required' => 'Välj en importtyp innan du fortsätter.',
        'processing' => 'Bearbetar din import. Vänta tills detta är klart innan du stänger sidan.',
        'backup_running' => 'Säkerhetskopiering körs före importen. Det kan ta en stund för större filer. Vänta.',
        'backup_label' => 'Backup före import',
        'backup_complete' => 'Backup klar',
        'import_label' => 'Importera',
        'required_fields_missing' => 'Följande obligatoriska fält är inte mappade: :fields',
        'history' => [
            'missing_asset_tag_identity' => '(saknar tillgångstagg)',
            'missing_asset_tag_message' => 'Rad hoppades över: ingen tillgångstagg angiven.',
            'asset_not_found_message' => 'En tillgång med denna tagg finns inte. Importera tillgångar först, kör sedan om historikimporten.',
            'target_not_matched_message' => 'Ingen :target_type matchade ”:name”. För användare kan du ändra matchningsalternativen i steg 1 eller skapa användaren först. För platser måste platsnamnet i CSV-filen exakt matcha en befintlig plats.',
            'invalid_target_type_message' => 'Måltypen ”:value” känns inte igen. Använd ”user” eller ”location”, eller lämna kolumnen tom för att använda standardvärdet user.',
        ],
        'wizard' => [
            'step_type' => 'Välj typ',
            'step_map' => 'Mappa fält',
            'step_preview' => 'Förhandsvisa',
            'back' => 'Bakåt',
            'next' => 'Nästa',
            'preview_button' => 'Förhandsvisa',
            'process' => 'Bearbeta import',
            'preview_intro' => 'Förhandsgranskning av de första :count raden/raderna efter att din mappning har tillämpats. Använd knappen Bakåt om du behöver redigera de mappade attributen innan import.',
        ],
    ],

    'delete' => [
        'confirm' => 'Är du säker på att du vill radera den här tillgången?',
        'error' => 'Det gick inte att ta bort tillgången. Var god försök igen.',
        'assigned_to_error' => '{1}Tillgångstagg: :asset_tag är för närvarande utcheckad. Checka in denna enhet innan radering.|[2,*]Tillgångstagg: :asset_tag är för närvarande utcheckade. Checka in dessa enheter innan radering.',
        'nothing_updated' => 'Inga tillgångar valdes. Ingenting togs bort.',
        'success' => 'Tillgång raderad.',
    ],

    'checkout' => [
        'error' => 'Tillgången kunde inte checkas ut, försök igen',
        'success' => 'Tillgången har checkats ut.',
        'user_does_not_exist' => 'Den användaren är ogiltig. Var god försök igen.',
        'not_available' => 'Den valda tillgången är inte tillgänglig för utcheckning.',
        'no_assets_selected' => 'Du måste välja minst en tillgång från listan',
    ],

    'multi-checkout' => [
        'error' => 'Tillgången har inte checkats ut, försök igen|Tillgångarna har inte checkats ut, försök igen',
        'success' => 'Utcheckning av tillgången lyckades.|Utcheckning av tillgångarna lyckades.',
    ],

    'multi-checkin' => [
        'error' => 'Tillgången kunde inte checkas in, försök igen|Tillgångarna kunde inte checkas in, försök igen',
        'success' => 'Tillgången har checkats in.|Tillgångarna har checkats in.',
        'no_assets_selected' => 'Du måste välja minst en tillgång från listan',
    ],

    'multi-audit' => [
        'success' => ':count tillgång har inventerats.|:count tillgångar har inventerats.',
        'partial_error' => ':success tillgång inventerad, :failed misslyckades. Kontrollera felen nedan och försök igen.|:success tillgångar inventerade, :failed misslyckades. Kontrollera felen nedan och försök igen.',
        'no_assets_selected' => 'Du måste välja minst en tillgång från listan',
    ],

    'checkin' => [
        'error' => 'Tillgången kunde inte checkas in, försök igen',
        'success' => 'Tillgången har checkats in.',
        'updated_without_checkin' => 'Asset was already checked in, but the form fields you set (status, location, name, notes) were applied anyway.',
        'user_does_not_exist' => 'Användaren är ogiltig. Var god försök igen.',
        'already_checked_in' => 'Tillgången är redan incheckad.',
        'force_checkin_orphaned_success' => 'Ogiltig tilldelning har rensats.',
        'force_checkin_not_orphaned' => 'Objektet är inte i ett tillstånd med ogiltig tilldelning.',
        'force_checkin_error' => 'Kunde inte rensa ogiltig tilldelning.',

    ],

    'requests' => [
        'error' => 'Begäran misslyckades. Försök igen.',
        'success' => 'Begäran har skickats in.',
        'canceled' => 'Begäran har avbrutits.',
        'cancel' => 'Avbryt objektbegäran',
        'duplicate' => 'Du har redan en aktiv begäran för denna tillgång.',
        'no_active' => 'Du har ingen aktiv begäran att avbryta för denna tillgång.',
        'insufficient_stock' => 'Det finns inte tillräckligt med lager för att uppfylla denna begäran. Fyll på lagret först.',
        'confirm_cancel_by_admin' => "Avbryt begäran från :user om :item?",
        'no_selection' => 'Inga rader har markerats för bearbetning.',
        'row_stale' => 'Begäran #:id är inte längre väntande och har hoppat över.',
        'row_qty_invalid' => 'Begäran #:id har en ogiltig kvantitet och har hoppat över.',
        'row_user_missing' => 'Begäran #:id har ingen giltig mottagare och har hoppat över.',
        'row_asset_missing' => 'Begäran #:id har ingen giltig tillgång som mål och har hoppat över.',
        'row_asset_not_requesters' => 'Begäran #:id hoppade över: den markerade tillgången tillhör inte begäraren.',
        'row_asset_not_available' => 'Begäran #:id hoppade över: den markerade tillgången är inte en tillgänglig enhet av denna modell.',
        'row_asset_taken' => 'Begäran #:id hoppade över: den markerade tillgången har redan tilldelats någon annan innan inlämning.',
        'row_company_mismatch' => 'Begäran #:id hoppade över: :user kan inte ta emot objekt från detta företag.',
        'row_over_allocated' => 'Begäran #:id hoppade över: det finns inte tillräckligt med lager vid tidpunkten för inlämning.',
        'no_target_assets_for_user' => ':user har inga tillgångar att installera i.',
        'no_available_units' => 'Inga tillgängliga enheter av denna modell att dela ut.',
        'bulk_summary' => 'Uppfyllde :fulfilled av :total begäranden.',
        'bulk_fulfill_notification_intro' => 'Följande kommer att gälla för varje markerad begäran när den uppfylls:',
    ],

];
