<?php

return [

    'update' => [
        'error'                 => 'Възникна грешка по време на актуализацията. ',
        'success'               => 'Настройките са актуализирани успешно.',
    ],
    'backup' => [
        'delete_confirm'        => 'Желаете ли изтриването на този архивен файл? Действието е окончателно.',
        'file_deleted'          => 'Архивният файл беше изтрит успешно.',
        'generated'             => 'Нов архивен файл беше създаден успешно.',
        'file_not_found'        => 'Архивният файл не беше открит на сървъра.',
        'restore_warning'       => 'Да, потвърди възстановяването. Това ще презапише цялата информация в датабазата и ще отпише всички вписани потребители включително вас.',
        'restore_confirm'       => 'Сигурни ли сте че искате да възстановите датабазата от :filename?'
    ],
    'restore' => [
        'success'               => 'Your system backup has been restored. Please log in again.'
    ],
    'purge' => [
        'error'     => 'Възникна грешка при пречистване. ',
        'validation_failed'     => 'Потвърждението ви за пречистване не неправилно. Моля напишете думата "DELETE" в клетката за потвърждаване.',
        'success'               => 'Изтрити записи успешно премахнати.',
    ],
    'mail' => [
        'sending' => 'Изпращане на тестов е-майл...',
        'success' => 'Писмото е изпратено!',
        'error' => 'Е-майла не може да се изпрати.',
        'additional' => 'Няма допълнителна информация за грешка. Проверете е-майл настройките и логовете на програмата.'
    ],
    'ldap' => [
        'testing' => 'Тест на LDAP връзката...',
        '500' => 'Грешка 500. Моля проверете логовете на сървъра за повече информация.',
        'error' => 'Възникна грешка :(',
        'sync_success' => 'Примерни 10 потребителя от LDAP сървъра базирани на вашите настройки:',
        'testing_authentication' => 'Тест LDAP Автентификация...',
        'authentication_success' => 'Потребителска Автентификация към LDAP успешна!'
    ],
    'labels' => [
        'null_template' => 'Label template not found. Please select a template.',
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
        'webhook_fail' => ' webhook notification failed: Check to make sure the URL is still valid.',
        'webhook_channel_not_found' => ' webhook channel not found.',
        'ms_teams_deprecation' => 'The selected Microsoft Teams webhook URL will be deprecated Dec 31st, 2025. Please use a workflow URL. Microsoft\'s documentation on creating a workflow can be found <a href="https://support.microsoft.com/en-us/office/create-incoming-webhooks-with-workflows-for-microsoft-teams-8ae491c7-0394-4861-ba59-055e33f75498" target="_blank"> here.</a>',
    ],
    'location_scoping' => [
        'not_saved' => 'Your settings were not saved.',
        'mismatch' => 'There is 1 item in the database that need your attention before you can enable location scoping.|There are :count items in the database that need your attention before you can enable location scoping.',
    ],
];
