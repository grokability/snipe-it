<?php

return [

    'undeployable' 		 => 'The following assets cannot be deployed and have been removed from checkout: :asset_tags',
    'does_not_exist' 	 => 'Основното средство не постои.',
    'does_not_exist_var' => 'Средство со ознака :asset_tag не е пронајдено.',
    'no_tag' 	         => 'Не е обезбедена ознака за средството.',
    'does_not_exist_or_not_requestable' => 'Тоа средство не постои или не е побараливо.',
    'assoc_users'	 	 => 'Ова средство е задолжено на корисник и не може да се избрише. Проверете го, а потоа пробајте повторно да го избришете. ',
    'warning_audit_date_mismatch' 	=> 'Следниот датум на ревизија на ова средство (:next_audit_date) е пред последниот датум на ревизија (:last_audit_date). Ажурирајте го следниот датум на ревизија.',
    'labels_generated'   => 'Labels were successfully generated.',
    'error_generating_labels' => 'Error while generating labels.',
    'no_assets_selected' => 'No assets selected.',

    'create' => [
        'error'   		=> 'Основното средство не е креирано, обидете се повторно. :(',
        'success' 		=> 'Основното средство е успешно креирано. :)',
        'success_linked' => 'Средство со ознака :tag беше создадено успешно. <strong><a href=":link" style="color: white;">Кликнете овде за да видите</a></strong>.',
        'multi_success_linked' => 'Asset with tag :links was created successfully.|:count assets were created succesfully. :links.',
        'partial_failure' => 'An asset was unable to be created. Reason: :failures|:count assets were unable to be created. Reasons: :failures',
        'target_not_found' => [
            'user' => 'The assigned user could not be found.',
            'asset' => 'The assigned asset could not be found.',
            'location' => 'The assigned location could not be found.',
        ],
    ],

    'update' => [
        'error'   			=> 'Основното средство не е ажурирано, обидете се повторно',
        'success' 			=> 'Основното средство е успешно ажурирано.',
        'encrypted_warning' => 'Средството успешно се ажурираше, но не беа енкиптираните полиња поради овластувањата',
        'nothing_updated'	=>  'Не беа избрани полиња, затоа ништо не беше ажурирано.',
        'no_assets_selected'  =>  'Не беа избрани средства, така што ништо не се ажурираше.',
        'assets_do_not_exist_or_are_invalid' => 'Избраните средства не можат да се ажурираат.',
    ],

    'restore' => [
        'error'   		=> 'Основното средство не е вратено, обидете се повторно',
        'success' 		=> 'Основното средство е успешно вратено.',
        'bulk_success' 		=> 'Основното средство е успешно вратено.',
        'nothing_updated'   => 'Не беа избрани средства, така што ништо не беше обновено.', 
    ],

    'audit' => [
        'error'   		=> 'Ревизија на средства неуспешна: :error ',
        'success' 		=> 'Ревизијата на основни средства е логирана.',
    ],


    'deletefile' => [
        'error'   => 'Датотеката не се избриша. Обидете се повторно.',
        'success' => 'Датотеката е успешно избришана.',
    ],

    'upload' => [
        'error'   => 'Датотеките не се прикачени. Обидете се повторно.',
        'success' => 'Успешно се преземени датотеките.',
        'nofiles' => 'Не одбравте датотеки за прикачување, или датотеката што сакате да ја поставите е премногу голема',
        'invalidfiles' => 'Една или повеќе од вашите датотеки е преголема или е тип на датотека што не е дозволен. Дозволени типови на датотеки се png, gif, jpg, doc, docx, pdf и txt.',
    ],

    'import' => [
        'import_button'         => 'Направи увоз',
        'error'                 => 'Некои ставки не се увезоа правилно.',
        'errorDetail'           => 'Следниве елементи не се увезени поради грешки.',
        'success'               => 'Вашата датотека е увезена',
        'file_delete_success'   => 'Вашата датотека е избришана',
        'file_delete_error'      => 'Датотеката не можеше да се избрише',
        'file_missing' => 'Избраната датотека недостасува',
        'file_already_deleted' => 'Избраната датотека е веќе избришана',
        'header_row_has_malformed_characters' => 'Еден или повеќе атрибути во заглавието се содржат неправилни UTF-8 карактери',
        'content_row_has_malformed_characters' => 'Еден или повеќе атрибути во првиот ред на содржина содржат неправилноUTF-8 карактери',
        'transliterate_failure' => 'Transliteration from :encoding to UTF-8 failed due to invalid characters in input'
    ],


    'delete' => [
        'confirm'   	=> 'Дали сте сигурни дека сакате да го избришете ова основно средство?',
        'error'   		=> 'Имаше проблем со бришење на основното средство. Обидете се повторно.',
        'assigned_to_error' => '{1}Asset Tag: :asset_tag is currently checked out. Check in this device before deletion.|[2,*]Asset Tags: :asset_tag are currently checked out. Check in these devices before deletion.',
        'nothing_updated'   => 'Не беа избрани основни средства, затоа ништо не беше избришано.',
        'success' 		=> 'Основното средство беше избришано.',
    ],

    'checkout' => [
        'error'   		=> 'Основното средство не беше задолжено, обидете се повторно',
        'success' 		=> 'Основното средство е задолжено.',
        'user_does_not_exist' => 'Корисникот е неважечки. Обидете се повторно.',
        'not_available' => 'Основното средство не е достапно за задолжување!',
        'no_assets_selected' => 'Мора да одберете најмалку едно основно средство',
    ],

    'multi-checkout' => [
        'error'   => 'Asset was not checked out, please try again|Assets were not checked out, please try again',
        'success' => 'Asset checked out successfully.|Assets checked out successfully.',
    ],

    'checkin' => [
        'error'   		=> 'Основното средство не беше раздолжено, обидете се повторно',
        'success' 		=> 'Основното средство е раздолжено.',
        'user_does_not_exist' => 'Корисникот е неважечки. Обидете се повторно.',
        'already_checked_in'  => 'Основното средство е веќе задолжено.',

    ],

    'requests' => [
        'error'   		=> 'Request was not successful, please try again.',
        'success' 		=> 'Request successfully submitted.',
        'canceled'      => 'Request successfully canceled.',
        'cancel'        => 'Откажи го ова барање',
    ],

];
