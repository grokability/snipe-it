<?php

return [

    'field' => [
        'invalid' => 'Tätä kenttää ei ole.',
        'already_added' => 'Kentässä jo lisätty',
        'none_selected' => 'Kenttää ei ole valittu',

        'create' => [
            'error' => 'Kenttää ei luotu, yritä uudelleen.',
            'success' => 'Kenttä luotiin onnistuneesti.',
            'assoc_success' => 'Kenttä lisättiin onnistuneesti kenttäsarjaan.',
        ],

        'update' => [
            'error' => 'Kenttä ei päivittynyt, yritä uudelleen',
            'success' => 'Kenttä päivitettiin onnistuneesti.',
        ],

        'delete' => [
            'confirm' => 'Haluatko varmasti poistaa tämän kentän?',
            'error' => 'Kentässä on ollut ongelma. Yritä uudelleen.',
            'success' => 'Kenttä poistettiin onnistuneesti.',
            'in_use' => 'Kenttä on edelleen käytössä.',
            'in_use_named' => ':name is still attached to at least one fieldset and cannot be deleted.',
            'bulk_success' => 'Field deleted successfully.|:count fields were deleted successfully.',
            'partial_success' => ':count field was deleted successfully, but others could not be deleted. See below for details.|:count fields were deleted successfully, but others could not be deleted. See below for details.',
        ],

    ],

    'fieldset' => [

        'does_not_exist' => 'Kenttäsarjaa ei ole',

        'create' => [
            'error' => 'Kenttäsarjaa ei luotu, yritä uudelleen.',
            'success' => 'Kenttäsarja luotu onnistuneesti.',
        ],

        'update' => [
            'error' => 'Kenttäsarjaa ei päivitetty, yritä uudelleen',
            'success' => 'Kenttäsarja päivitettiin onnistuneesti.',
        ],

        'delete' => [
            'confirm' => 'Haluatko varmasti poistaa tämän kenttäsarjan?',
            'error' => 'Kenttäsarjan poistamisessa tapahtui virhe. Yritä uudelleen.',
            'success' => 'Kenttäsarja poistettiin onnistuneesti.',
            'in_use' => 'Kenttäsarja on edelleen käytössä.',
            'in_use_named' => ':name is still attached to at least one asset model and cannot be deleted.',
            'bulk_success' => 'Fieldset deleted successfully.|:count fieldsets were deleted successfully.',
            'partial_success' => ':count fieldset was deleted successfully, but others could not be deleted. See below for details.|:count fieldsets were deleted successfully, but others could not be deleted. See below for details.',
        ],

        'clone' => [
            'success' => 'Fieldset cloned successfully.',
        ],

    ],

    'fieldset_default_value' => [

        'error' => 'Virhe varmentaessa kentän oletusarvoja.',

    ],

];
