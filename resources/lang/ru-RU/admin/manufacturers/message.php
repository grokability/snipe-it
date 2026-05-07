<?php

return [

    'support_url_help' => 'Переменные <code>{LOCALE}</code>, <code>{SERIAL}</code>, <code>{MODEL_NUMBER}</code>, и <code>{MODEL_NAME}</code> могут быть использованы в URL для автозаполнения значений при просмотре активов - например, https://checkcoverage. pple.com/{LOCALE}/{SERIAL}.',
    'does_not_exist' => 'Производителя не существует.',
    'assoc_users' => 'Этот производитель сейчас связан с как минимум одной моделью и не может быть удален. Обновите модели, чтобы они не ссылались на этого производителя, и попробуйте снова. ',

    'create' => [
        'error' => 'Не удалось создать производителя, попробуйте снова.',
        'success' => 'Производитель создан.',
    ],

    'update' => [
        'error' => 'Производитель не был обновлен, попробуйте снова',
        'success' => 'Производитель обновлён.',
    ],

    'restore' => [
        'error' => 'Не удалось восстановить производителя, попробуйте снова',
        'success' => 'Производитель восстановлен.',
    ],

    'delete' => [
        'confirm' => 'Вы действительно хотите удалить производителя?',
        'error' => 'При удалении производителя возникла ошибка. Попробуйте снова.',
        'success' => 'Manufacturer deleted successfully.',
        'bulk_success' => 'Manufacturers deleted successfully.',
        'partial_success' => 'Manufacturer deleted successfully. See additional information below. | :count manufacturers were deleted successfully. See additional information below.',
    ],

];
