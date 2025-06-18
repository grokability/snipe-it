<?php

return [

    'update' => [
        'error'                 => 'Khalad ayaa dhacay markii la cusboonaysiiyay ',
        'success'               => 'Cusbooneysiinta qaabeynta waa lagu guuleystay.',
    ],
    'backup' => [
        'delete_confirm'        => 'Ma hubtaa inaad jeclaan lahayd inaad tirtirto faylka kaydka ah? Tallaabadan lama noqon karo. ',
        'file_deleted'          => 'Faylka kaabta ayaa si guul leh loo tirtiray ',
        'generated'             => 'Feyl cusub oo keyd ah ayaa lagu guuleystay in la abuuro.',
        'file_not_found'        => 'Faylkaas kaydka ah ayaa laga waayay seerfarka.',
        'restore_warning'       => 'Haa, soo celi Waxaan qirayaa in tani ay dib u qori doonto xog kasta oo hadda ku jirta kaydka xogta. Tani waxay sidoo kale ka saari doontaa dhammaan isticmaalayaashaada jira (oo ay ku jirto adiga).',
        'restore_confirm'       => 'Ma hubtaa inaad rabto inaad ka soo celiso xogtaada: filename?'
    ],
    'restore' => [
        'success'               => 'Your system backup has been restored. Please log in again.'
    ],
    'purge' => [
        'error'     => 'Khalad ayaa dhacay markii la nadiifinayo ',
        'validation_failed'     => 'Xaqiijinta nadiifintaadu waa khalad. Fadlan ku qor kelmadda "DELETE" sanduuqa xaqiijinta.',
        'success'               => 'Diiwaanada la tirtiray si guul leh ayaa loo nadiifiyay.',
    ],
    'mail' => [
        'sending' => 'Diraya Iimayl tijaabo ah...',
        'success' => 'Boostada waa la soo diray!',
        'error' => 'Email lama diri karo.',
        'additional' => 'Ma jiro fariin khalad ah oo dheeri ah oo la bixiyay Hubi dejimahaaga fariimaha iyo logkaaga abka.'
    ],
    'ldap' => [
        'testing' => 'Tijaabinta Xidhiidhka LDAP, Ku xidhka & Weydiinta...',
        '500' => '500 Cilad Server Fadlan hubi diiwaanka server-kaaga wixii macluumaad dheeraad ah.',
        'error' => 'Waxbaa qaldamay :(',
        'sync_success' => 'Muunad 10 isticmaale ah ayaa laga soo celiyay server-ka LDAP iyadoo lagu salaynayo habayntaada:',
        'testing_authentication' => 'Tijaabi aqoonsiga LDAP...',
        'authentication_success' => 'Isticmaaluhu wuxuu ka xaqiijiyay LDAP si guul leh!'
    ],
    'labels' => [
        'null_template' => 'Label template not found. Please select a template.',
        ],
    'webhook' => [
        'sending' => 'Diraya :app fariinta tijaabada abka...',
        'success' => 'Magacaaga:webhook_name Isdhexgalka wuu shaqeeyaa!',
        'success_pt1' => 'Guul! Hubi ',
        'success_pt2' => ' kanaalka fariinta tijaabada ah, oo hubi inaad gujiso SAVE xagga hoose si aad u kaydiso dejintaada.',
        '500' => '500 Cilad Server.',
        'error' => 'Waxbaa qaldamay. :app waxa uu kaga jawaabay: : error_message',
        'error_redirect' => 'CILAD: 301/302 :endpoint Sababo ammaan dartood, ma raacno dib u jiheynta Fadlan isticmaal barta dhamaadka dhabta ah.',
        'error_misc' => 'Waxbaa qaldamay. :( ',
        'webhook_fail' => ' webhook notification failed: Check to make sure the URL is still valid.',
        'webhook_channel_not_found' => ' webhook channel not found.',
        'ms_teams_deprecation' => 'The selected Microsoft Teams webhook URL will be deprecated Dec 31st, 2025. Please use a workflow URL. Microsoft\'s documentation on creating a workflow can be found <a href="https://support.microsoft.com/en-us/office/create-incoming-webhooks-with-workflows-for-microsoft-teams-8ae491c7-0394-4861-ba59-055e33f75498" target="_blank"> here.</a>',
    ],
    'location_scoping' => [
        'not_saved' => 'Your settings were not saved.',
        'mismatch' => 'There is 1 item in the database that need your attention before you can enable location scoping.|There are :count items in the database that need your attention before you can enable location scoping.',
    ],
];
