<?php

return array(

    'support_url_help' => 'Змінні <code>{LOCALE}</code>, <code>{SERIAL}</code>, <code>{MODEL_NUMBER}</code> і <code>{MODEL_NAME}</code> можна використовувати в URL-адресі для автоматичного заповнення цих значень під час перегляду ресурсів, наприклад https://checkcoverage.apple.com/{LOCALE}/{SERIAL}.',
    'does_not_exist' => 'Виробник не існує.',
    'assoc_users'	 => 'Цього виробника зараз пов\'язано принаймні з однією моделлю та не може бути видалений. Будь ласка, оновіть ваші моделі, щоб більше не посилатися на виробника та повторіть спробу. ',

    'create' => array(
        'error'   => 'Виробник не був створений, будь ласка, спробуйте ще раз.',
        'success' => 'Виробник успішно створений.'
    ),

    'update' => array(
        'error'   => 'Виробник не був оновлений, спробуйте ще раз',
        'success' => 'Виробника успішно оновлено.'
    ),

    'restore' => array(
        'error'   => 'Виробник не був відновлений, спробуйте ще раз',
        'success' => 'Виробник успішно відновлений.'
    ),

    'delete' => array(
        'confirm' => 'Ви впевнені що хочете видалити виробника?',
        'error'   => 'Виникла проблема при видаленні виробника. Будь ласка, спробуйте знову.',
        'success'                => 'Manufacturer deleted successfully.',
        'bulk_success'           => 'Manufacturers deleted successfully.',
        'partial_success'        => 'Manufacturer deleted successfully. See additional information below. | :count manufacturers were deleted successfully. See additional information below.',
    )

);
