<?php

return array(

    'does_not_exist' => 'Компонентата не постои.',

    'create' => array(
        'error'   => 'Компонентата не е креирана, обидете се повторно.',
        'success' => 'Компонентата е успешно креирана.'
    ),

    'update' => array(
        'error'   => 'Компонентата не беше ажурирана, обидете се повторно',
        'success' => 'Компонентата е успешно ажурирана.'
    ),

    'delete' => array(
        'confirm'   => 'Дали сте сигурни дека сакате да ја избришете оваа компонента?',
        'error'   => 'Имаше проблем со бришење на компонентата. Обидете се повторно.',
        'success' => 'Компонентата беше успешно избришана.',
        'error_qty'   => 'Some components of this type are still checked out. Please check them in and try again.',
    ),

     'checkout' => array(
        'error'   		=> 'Компонентата не беше задолжена, обидете се повторно',
        'success' 		=> 'Компонентата е задолжена.',
        'user_does_not_exist' => 'Тој корисник е неважечки. Обидете се повторно.',
        'unavailable'      => 'Нема доволно компоненти: :remaining remaining, :requested requested ',
    ),

    'checkin' => array(
        'error'   		=> 'Компонентата не беше раздолжена, обидете се повторно',
        'success' 		=> 'Компонентата е раздолжена.',
        'user_does_not_exist' => 'Тој корисник е неважечки. Обидете се повторно.'
    )


);
