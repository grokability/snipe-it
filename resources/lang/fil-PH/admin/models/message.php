<?php

return array(

    'deleted' => 'Deleted asset model',
    'does_not_exist' => 'Ang modelo ay hindi umiiral.',
    'no_association' => 'WARNING! The asset model for this item is invalid or missing!',
    'no_association_fix' => 'This will break things in weird and horrible ways. Edit this asset now to assign it a model.',
    'assoc_users'	 => 'Ang modelong ito ay kasalukuyang nai-ugnay sa isa o higit pang mga asset at hindi maaaring mai-delete. Paki-delete ng mga model na ito, at pagkatapos subukang i-delete muli. ',
    'invalid_category_type' => 'This category must be an asset category.',

    'create' => array(
        'error'   => 'Ang modelo ay hindi naisagawa, mangyaring subukang muli.',
        'success' => 'Ang modelo ay matagumpay na naisagawa.',
        'duplicate_set' => 'Ang modelo ng asset na may ganyang pangalan, ang tagapagsagawa at ang modelo ay umiiral na.',
    ),

    'update' => array(
        'error'   => 'Ang modelo ay hindi nai-update, mangyaring subukang muli',
        'success' => 'Ang modelo ay matagumpay na nai-update.',
    ),

    'delete' => array(
        'confirm'   => 'Sigurado kaba na gusto mong i-delete ang modelo ng asset?',
        'error'   => 'Mayroong isyu sa pag-delete ng modelo. Mangyaring subukang muli.',
        'success' => 'Matagumpay na nai-delete ang modelo.'
    ),

    'restore' => array(
        'error'   		=> 'Ang modelo ay hindi naibalik sa dati, mangyaring subukang muli',
        'success' 		=> 'Ang modelo ay matagumpay na naibalik.'
    ),

    'bulkedit' => array(
        'error'   		=> 'Walang nabagong mga field, kaya walang nai-update.',
        'success' 		=> 'Model successfully updated. |:model_count models successfully updated.',
        'warn'          => 'You are about to update the properties of the following model:|You are about to edit the properties of the following :model_count models:',

    ),

    'bulkdelete' => array(
        'error'   		    => 'Walang napiling mga model, kaya walang nai-delete.',
        'success' 		    => 'Model deleted!|:success_count models deleted!',
        'success_partial' 	=> ':success_count ang mga modelo ay na-delete na, gayunpaman ::success_count ang mga modelo ay hindi mai-delete dahil sa mayron pa silang asset na naiuugnay sa kanila.'
    ),

);
