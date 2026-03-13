<?php

return array(

    'support_url_help' => 'Zmienne <code>{LOCALE}</code>, <code>{SERIAL}</code>, <code>{MODEL_NUMBER}</code>, i <code>{MODEL_NAME}</code> może być używany w Twoim adresie URL, aby wartości te były automatycznie wypełniane podczas oglądania aktywów - na przykład https://checkcoverage.apple.com/{LOCALE}/{SERIAL}.',
    'does_not_exist' => 'Producent nie istnieje.',
    'assoc_users'	 => 'Wybrany producent jest obecnie skojarzony z minimum jednym modelem i nie może zostać usunięty. Uaktualnij swoją listę modeli urządzeń by nie zawierała tego producenta, a następnie spróbuj ponownie. ',

    'create' => array(
        'error'   => 'Producent nie został stworzony, spróbuj ponownie.',
        'success' => 'Producent stworzony pomyślnie.'
    ),

    'update' => array(
        'error'   => 'Producent nie został zaktualizowany, spróbuj ponownie',
        'success' => 'Producent zaktualizowany pomyślnie.'
    ),

    'restore' => array(
        'error'   => 'Producent nie został przywrócony, spróbuj ponownie.',
        'success' => 'Producent przywrócony pomyślnie.'
    ),

    'delete' => array(
        'confirm' => 'Czy na pewno usunąć danego producenta?',
        'error'   => 'Podczas usuwania producenta wystąpił błąd. Spróbuj ponownie.',
        'success'                => 'Pomyślnie usunięto producenta.',
        'bulk_success'           => 'Pomyślnie usunięto producentów.',
        'partial_success'        => 'Pomyślnie usunięto producenta. Więcej informacji poniżej. |Pomyślnie usunięto :count producentów. Więcej informacji poniżej.',
    )

);
