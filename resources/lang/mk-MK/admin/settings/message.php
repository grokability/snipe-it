<?php

return [

    'update' => [
        'error'                 => 'Се случи грешка при ажурирањето. ',
        'success'               => 'Поставките се ажурирани.',
    ],
    'backup' => [
        'delete_confirm'        => 'Дали си сигурен дека сакаш да ја избришеш резервната копија? Ова не може да биде вратено. ',
        'file_deleted'          => 'Резервната копија е избришана. ',
        'generated'             => 'Направена е нова резервна копија.',
        'file_not_found'        => 'Таа резервна копија не може да се најде на серверот.',
        'restore_warning'       => 'Да, врати. Потврдувам дека ова ќе ги презапише сите постоечки податоци во моментот во базата на податоци. Ова исто така ќе ги одјави сите постоечки корисници This will also log out all of your existing users (вклучително и тебе).',
        'restore_confirm'       => 'Дали сте сигурни дека сакате да ја вратите вашата база на податоци од :filename?'
    ],
    'restore' => [
        'success'               => 'Резервната копија на вашиот систем е обновена. Повторно најавете се.'
    ],
    'purge' => [
        'error'     => 'Се случи грешка при трајното бришење. ',
        'validation_failed'     => 'Потврдата за трајно бришење е неточна. Внесете го зборот "DELETE" во полето за потврда.',
        'success'               => 'Записите се трајно избришани.',
    ],
    'mail' => [
        'sending' => 'Испраќање на тест е-пошта...',
        'success' => 'Испратена пошта!',
        'error' => 'Пошта не може да се испрати.',
        'additional' => 'Не е обезбедена дополнителна порака за грешка. Проверете ги поставките за пошта и дневникот за апликации.'
    ],
    'ldap' => [
        'testing' => 'Тестирање LDAP Конекција, Поврзување и пребарување ...',
        '500' => '500 Серверска грешка. Проверете ги дневниците на вашиот сервер за повеќе информации.',
        'error' => 'Нешто не е во ред :(',
        'sync_success' => 'Примерок од 10 корисници е вратен од LDAP серверот на основа на вашите подесувања:',
        'testing_authentication' => 'Тестирање LDAP Автентификација...',
        'authentication_success' => 'Автентификацијата на корисникот на LDAP е успешна!'
    ],
    'labels' => [
        'null_template' => 'Label template not found. Please select a template.',
        ],
    'webhook' => [
        'sending' => 'Испраќање :app тест порака...',
        'success' => 'Вашата :webhook_name Интеграција работи!',
        'success_pt1' => 'Успех! Проверете ',
        'success_pt2' => ' канал за вашата тест порака, и не заборавајте да кликнете на Зачувај подолу за да ги зачувате поставките.',
        '500' => '500 Серверска грешка.',
        'error' => 'Нешто не е во ред. :app одговори со :error_message',
        'error_redirect' => 'ГРЕШКА: 301/302 :endpoint враќа пренасочување. Од безбедносни причини, ние не ги следиме пренасочувањата. Ве молиме користете директна крајна точка.',
        'error_misc' => 'Нешто не е во ред. :( ',
        'webhook_fail' => ' webhook notification failed: Check to make sure the URL is still valid.',
        'webhook_channel_not_found' => ' webhook channel not found.',
        'ms_teams_deprecation' => 'The selected Microsoft Teams webhook URL will be deprecated Dec 31st, 2025. Please use a workflow URL. Microsoft\'s documentation on creating a workflow can be found <a href="https://support.microsoft.com/en-us/office/create-incoming-webhooks-with-workflows-for-microsoft-teams-8ae491c7-0394-4861-ba59-055e33f75498" target="_blank"> here.</a>',
    ],
    'location_scoping' => [
        'not_saved' => 'Your settings were not saved.',
        'mismatch' => 'There is 1 item in the database that need your attention before you can enable location scoping.|There are :count items in the database that need your attention before you can enable location scoping.',
    ],
];
