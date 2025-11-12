<?php

return array(

    'does_not_exist' => 'Nie ma takiej kategorii.',
    'assoc_models'	 => 'Wybrana kategoria jest obecnie powiązana z co najmniej jednym typem urządzenia i nie może zostać usunięta. Uaktualnij swoją listę modeli urządzeń by nie zwierała tej kategorii, a następnie spróbuj ponownie. ',
    'assoc_items'	 => 'Ta kategoria jest obecnie powiązana z co najmniej jednym :asset_type i nie można jej usunąć. Uaktualnij swój :asset_type tak, aby nie odnosiła się do tej kategorii, a następnie spróbuj ponownie. ',

    'create' => array(
        'error'   => 'Kategoria nie została utworzona, spróbuj ponownie.',
        'success' => 'Kategoria utworzona.'
    ),

    'update' => array(
        'error'   => 'Kategoria nie została zaktualizowana, spróbuj ponownie',
        'success' => 'Kategoria zaktualizowana.',
        'cannot_change_category_type'   => 'Nie można zmienić typu kategorii po jej utworzeniu',
    ),

    'delete' => array(
        'confirm'                => 'Czy jesteś pewien że chcesz usunąć tą kategorię ?',
        'error'                  => 'Wystąpił błąd podczas usuwania kategorii. Spróbuj ponownie.',
        'success'                => 'Pomyślnie usunięto kategorię.',
        'bulk_success'           => 'Pomyślnie usunięto kategorie.',
        'partial_success'        => 'Kategoria usunięta z powodzeniem. Sprawdź szczegółowe informacje poniżej. | :count kategorie zostały usunięte z powodzeniem. Sprawdź szczegółowe informacje poniżej.',
    )

);
