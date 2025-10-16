<?php

return array(

    'does_not_exist' => 'Розташування не існує.',
    'assoc_users'    => 'This location is not currently deletable because it is the location of record for at least one item or user, has assets assigned to it, or is the parent location of another location. Please update your records to no longer reference this location and try again ',
    'assoc_assets'	 => 'Це розташування в даний час пов\'язано принаймні з одним активом і не може бути видалений. Будь ласка, оновіть ваші медіафайли, щоб більше не посилатися на це розташування і повторіть спробу. ',
    'assoc_child_loc'	 => 'Це місцезнаходження наразі батько принаймні одного дочірнього місця і не може бути видалений. Будь ласка, оновіть ваше місцеположення, щоб більше не посилатися на це місце і повторіть спробу. ',
    'assigned_assets' => 'Призначені активи',
    'current_location' => 'Поточне місцезнаходження',
    'open_map' => 'Відкрити в картах :map_provider_icon',
    'deleted_warning' => 'This location has been deleted. Please restore it before attempting to make any changes.',


    'create' => array(
        'error'   => 'Місце не створено, спробуйте ще раз.',
        'success' => 'Розташування успішно створено.'
    ),

    'update' => array(
        'error'   => 'Розташування не було оновлено, спробуйте ще раз',
        'success' => 'Розташування успішно створено.'
    ),

    'restore' => array(
        'error'   => 'Розташування не було відновлено, спробуйте ще раз',
        'success' => 'Розташування успішно відновлено.'
    ),

    'delete' => array(
        'confirm'   	=> 'Ви впевнені, що хочете видати це розташування?',
        'error'   => 'Виникла проблема з видаленням місцезнаходження. Будь ласка, спробуйте ще раз.',
        'success' => 'Розташування було успішно видалено.'
    )

);
