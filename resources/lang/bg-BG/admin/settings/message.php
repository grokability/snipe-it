<?php

return [

    'update' => [
        'error' => 'Възникна грешка по време на актуализацията. ',
        'success' => 'Настройките са актуализирани успешно.',
    ],
    'backup' => [
        'delete_confirm' => 'Желаете ли изтриването на този архивен файл? Действието е окончателно.',
        'file_deleted' => 'Архивният файл беше изтрит успешно.',
        'generated' => 'Нов архивен файл беше създаден успешно.',
        'file_not_found' => 'Архивният файл не беше открит на сървъра.',
        'restore_warning' => 'Да, потвърди възстановяването. Това ще презапише цялата информация в датабазата и ще отпише всички вписани потребители включително вас.',
        'restore_confirm' => 'Сигурни ли сте че искате да възстановите датабазата от :filename?',
    ],
    'restore' => [
        'success' => 'Вашият системен архив беше възстановен. Моля влезте отново.',
    ],
    'purge' => [
        'error' => 'Възникна грешка при пречистване. ',
        'validation_failed' => 'Потвърждението ви за пречистване не неправилно. Моля напишете думата "DELETE" в клетката за потвърждаване.',
        'success' => 'Изтрити записи успешно премахнати.',
    ],
    'mail' => [
        'sending' => 'Изпращане на тестов е-майл...',
        'success' => 'Писмото е изпратено!',
        'error' => 'Е-майла не може да се изпрати.',
        'additional' => 'Няма допълнителна информация за грешка. Проверете е-майл настройките и логовете на програмата.',
    ],
    'ldap' => [
        'testing' => 'Тест на LDAP връзката...',
        '500' => 'Грешка 500. Моля проверете логовете на сървъра за повече информация.',
        'error' => 'Възникна грешка :(',
        'sync_success' => 'Примерни 10 потребителя от LDAP сървъра базирани на вашите настройки:',
        'testing_authentication' => 'Тест LDAP Автентификация...',
        'authentication_success' => 'Потребителска Автентификация към LDAP успешна!',
    ],
    'labels' => [
        'null_template' => 'Шаблона за етикета не е намерен. Моля изберете друг шаблон.',
    ],
    'webhook' => [
        'sending' => 'Изпращане :app тест съобщение...',
        'success' => 'Вашата :webhook_name интеграция работи!',
        'success_pt1' => 'Успешно! Проверете ',
        'success_pt2' => ' канал за вашето тестово съобщение и натиснете бутона SAVE за да запазите вашите настройки.',
        '500' => 'Грешка 500.',
        'error' => 'Възникна грешка. :app върна грешка: :error_message',
        'error_redirect' => 'Грешка 301/302 :endpoint върна пренасочване. От съображения за сигурност, ние не отваряме пренасочванията. Моля ползвайте действителната крайна точка.',
        'error_misc' => 'Възникна грешка. :( ',
        'webhook_fail' => ' webhook известието неуспешно: Проверете URL адреса.',
        'webhook_channel_not_found' => ' webhook канала не е намерен.',
        'ms_teams_deprecation' => 'Избраният Microsoft Teams webhook URL ще бъде излязал от употреба от 31 дек 2025 г. Моля ползвайте workflow URL. Документация как да създадете workflow може да бъде намерена <a href="https://support.microsoft.com/en-us/office/create-incoming-webhooks-with-workflows-for-microsoft-teams-8ae491c7-0394-4861-ba59-055e33f75498" target="_blank"> тук.</a>',
    ],
    'location_scoping' => [
        'not_saved' => 'Вашите настройки не бяха записани.',
        'mismatch' => 'В базата данни има 1 елемент, който изисква вашето внимание, преди да можете да активирате обхвата на местоположението.|В базата данни има :count елементи, които изискват вашето внимание, преди да можете да активирате обхвата на местоположението.',
    ],
    'oauth' => [
        'token_revoked' => 'Personal access token revoked successfully.',
        'token_unrevoked' => 'Personal access token reinstated successfully.',
        'token_not_found' => 'That personal access token could not be found.',
        'token_revoke_error' => 'An error occurred while revoking the token.',
        'token_unrevoke_error' => 'An error occurred while reinstating the token.',
        'client_created' => 'OAuth client created successfully.',
        'client_updated' => 'OAuth client updated successfully.',
        'client_deleted' => 'OAuth client deleted successfully.',
        'client_revoked' => 'OAuth client revoked successfully.',
        'client_unrevoked' => 'OAuth client reinstated successfully.',
        'client_not_found' => 'That OAuth client could not be found.',
        'token_deleted' => 'Token revoked successfully.',
        'client_delete_denied' => 'You are not authorized to delete this client.',
        'client_edit_denied' => 'You are not authorized to edit this client.',
        'token_delete_denied' => 'You are not authorized to revoke this token.',
    ],
];
