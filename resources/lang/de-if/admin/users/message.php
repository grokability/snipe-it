<?php

return array(

    'accepted'                  => 'Du hast den Gegenstand erfolgreich angenommen.',
    'declined'                  => 'Du hast diesen Gegenstand erfolgreich abgelehnt.',
    'bulk_manager_warn'	        => 'Deine Benutzer wurden erfolgreich aktualisiert, aber dein Manager-Eintrag wurde nicht gespeichert, da der Manager, den du ausgewählt hast, auch in der zu bearbeitenden Liste war, und Benutzer dürfen nicht ihr eigener Manager sein. Bitte wähle deine Benutzer erneut aus, ohne diesen Manager.',
    'user_exists'               => 'Benutzer existiert bereits!',
    'cannot_delete'             => 'Benutzer existiert nicht oder du hast nicht die Berechtigung zum Löschen.',
    'user_not_found'            => 'Benutzer existiert nicht oder Sie haben keine Berechtigung, sie anzusehen.',
    'user_login_required'       => 'Das Loginfeld ist erforderlich',
    'user_has_no_assets_assigned' => 'Derzeit sind keine Assets dem Benutzer zugewiesen.',
    'user_password_required'    => 'Das Passswortfeld ist erforderlich.',
    'insufficient_permissions'  => 'Unzureichende Berechtigungen.',
    'user_deleted_warning'      => 'Dieser Benutzer wurde gelöscht. Du musst ihn wiederherstellen, um ihn zu bearbeiten, oder neue Assets zuzuweisen.',
    'ldap_not_configured'        => 'LDAP Integration wurde für diese Installation nicht konfiguriert.',
    'password_resets_sent'      => 'Den ausgewählten Benutzern, die aktiviert sind und eine gültige E-Mail-Adresse haben, wurde ein Link zum Zurücksetzen des Passworts gesendet.',
    'password_reset_sent'       => 'Der Link zum Zurücksetzen des Passworts wurde an :email gesendet!',
    'user_has_no_email'         => 'Dieser Benutzer hat keine E-Mail-Adresse in seinem Profil.',
    'log_record_not_found'        => 'Ein passender Logeintrag für diesen Benutzer konnte nicht gefunden werden.',


    'success' => array(
        'create'    => 'Benutzer wurde erfolgreich erstellt.',
        'update'    => 'Benutzer wurde erfolgreich aktualisiert.',
        'update_bulk'    => 'Benutzer erfolgreich aktualisiert!',
        'delete'    => 'Benutzer wurde erfolgreich gelöscht.',
        'ban'       => 'Benutzer wurde erfolgreich ausgeschlossen.',
        'unban'     => 'Benutzer wurde erfolgreich wieder aufgenommen.',
        'suspend'   => 'Benutzer wurde erfolgreich suspendiert.',
        'unsuspend' => 'Der Benutzer wurde erfolgreich reaktiviert.',
        'restored'  => 'Benutzer wurde erfolgreich wiederhergestellt.',
        'import'    => 'Benutzer erfolgreich Importiert.',
    ),

    'error' => array(
        'create' => 'Beim Erstellen des Benutzers ist ein Fehler aufgetreten. Bitte probiere es noch einmal.',
        'update' => 'Beim Aktualisieren des Benutzers ist ein Fehler aufgetreten. Bitte probiere es noch einmal.',
        'delete' => 'Beim Löschen des Benutzers ist ein Fehler aufgetreten. Bitte versuche es erneut.',
        'delete_has_assets' => 'Der Benutzer konnte nicht gelöscht werden, da ihm Gegenstände zugeordnet sind.',
        'delete_has_assets_var' => 'Dieser Benutzer hat noch ein Asset zugewiesen. Bitte nehme es zuerst zurück.|Dieser Benutzer hat noch :count Assets zugewiesen. Bitte nehme diese zuerst zurück.',
        'delete_has_licenses_var' => 'Dieser Benutzer hat noch einen Lizenzplatz zugewiesen. Bitte nehme es zuerst zurück.|Dieser Benutzer hat noch :count Lizenzplätze zugewiesen. Bitte nehme diese zuerst zurück.',
        'delete_has_accessories_var' => 'Dieser Benutzer hat noch ein Zubehör zugewiesen. Bitte nehme es zuerst zurück.|Dieser Benutzer hat noch :count Zubehöre zugewiesen. Bitte nehme diese zuerst zurück.',
        'delete_has_locations_var' => 'Dieser Benutzer verwaltet noch einen anderen Standort. Bitte wähle zuerst einen anderen Manager.|Dieser Benutzer verwaltet noch :count Standorte. Bitte wähle zuerst einen anderen Manager.',
        'delete_has_users_var' => 'Dieser Benutzer verwaltet noch einen anderen Benutzer. Bitte wähle zuerst einen anderen Manager für diesen Benutzer.|Dieser Benutzer verwaltet noch :count Benutzer. Bitte wähle zuerst einen anderen Manager.',
        'unsuspend' => 'Es gab ein Problem beim reaktivieren des Benutzers. Bitte versuche es erneut.',
        'import'    => 'Es gab ein Problem beim Importieren der Benutzer. Bitte noch einmal versuchen.',
        'asset_already_accepted' => 'Dieses Asset wurde bereits akzeptiert.',
        'accept_or_decline' => 'Du musst diesen Gegenstand entweder annehmen oder ablehnen.',
        'cannot_delete_yourself' => 'Wir würden uns wirklich schlecht fühlen, wenn du dich selbst löschen würdest. Überlege es dir bitte noch einmal.',
        'incorrect_user_accepted' => 'Das Asset, das du annehmen willst, wurde nicht an dich ausgecheckt.',
        'ldap_could_not_connect' => 'Konnte keine Verbindung zum LDAP Server herstellen. Bitte LDAP Einstellungen in der LDAP Konfigurationsdatei prüfen. <br>Fehler vom LDAP Server:',
        'ldap_could_not_bind' => 'Konnte keine Verbindung zum LDAP Server herstellen. Bitte LDAP Einstellungen in der LDAP Konfigurationsdatei prüfen. <br>Fehler vom LDAP Server: ',
        'ldap_could_not_search' => 'Konnte LDAP Server nicht suchen. Bitte LDAP Einstellungen in der LDAP Konfigurationsdatei prüfen. <br>Fehler vom LDAP Server:',
        'ldap_could_not_get_entries' => 'Konnte keine Einträge vom LDAP Server abrufen. Bitte LDAP Einstellungen in der LDAP Konfigurationsdatei prüfen. <br>Fehler vom LDAP Server:',
        'password_ldap' => 'Das Passwort für diesen Account wird vom LDAP/Active Directory verwaltet. Bitte kontaktiere Deine IT-Abteilung, um Dein Passwort zu ändern. ',
        'multi_company_items_assigned' => 'Diesem Benutzer sind Dinge zugewiesen, die zu einer anderen Firma gehören. Bitte checke sie ein oder bearbeite deine Firma.'
    ),

    'deletefile' => array(
        'error'   => 'Datei wurde nicht gelöscht. Bitte versuche es erneut.',
        'success' => 'Datei erfolgreich gelöscht.',
    ),

    'upload' => array(
        'error'   => 'Datei(en) wurde(n) nicht hochgeladen. Bitte versuche es erneut.',
        'success' => 'Datei(en) wurden erfolgreich hochgeladen.',
        'nofiles' => 'Du hast keine Dateien zum Hochladen ausgewählt',
        'invalidfiles' => 'Eine oder mehrere Deiner Dateien sind zu groß, oder deren Dateityp ist nicht zugelassen. Zugelassene Dateitypen sind png, gif, jpg, doc, docx, pdf, und txt.',
    ),

    'inventorynotification' => array(
        'error'   => 'Für diesen Benutzer ist keine E-Mail-Adresse hinterlegt.',
        'success' => 'Der Benutzer wurde über sein aktuelles Inventar informiert.'
    )
);