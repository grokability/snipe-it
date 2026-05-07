<?php

return [

    'deleted' => 'Model aset yang dipadam',
    'does_not_exist' => 'Model tidak wujud.',
    'no_association' => 'AMARAN! Model aset untuk item ini tidak sah atau tiada!',
    'no_association_fix' => 'Ini akan menyebabkan masalah yang pelik dan teruk. Sila sunting aset ini sekarang untuk menetapkan model kepadanya.',
    'assoc_users' => 'Model ini sekarang disekutukan dengan sekurang2nya satu atau lebih harta dan tidak boleh dihapuskan. Sila kemaskini harta, dan kemudian cuba lagi. ',
    'invalid_category_type' => 'Kategori ini mesti merupakan kategori aset.',

    'create' => [
        'error' => 'Model gagal dicipta, sila cuba lagi.',
        'success' => 'Model berjaya dicipta.',
        'duplicate_set' => 'Model aset dengan nama itu, pengeluar dan nombor model sudah ada.',
    ],

    'update' => [
        'error' => 'Model gagal dikemaskin, sila cuba lagi',
        'success' => 'Model berjaya dikemaskini.',
    ],

    'delete' => [
        'confirm' => 'Anda pasti anda ingin hapuskan model harta ini?',
        'error' => 'Ada isu semasa menghapuskan model. Sila cuba lagi.',
        'success' => 'Model berjaya dihapuskan.',
    ],

    'restore' => [
        'error' => 'Model tidak dipulihkan, sila cuba lagi',
        'success' => 'Model berjaya dipulihkan.',
    ],

    'bulkedit' => [
        'error' => 'Tiada medan berubah, jadi tiada apa yang dikemas kini.',
        'success' => 'Model berjaya dikemas kini. |:model_count model berjaya dikemas kini.',
        'warn' => 'Anda akan mengemas kini sifat model berikut: | Anda akan menyunting sifat :model_count model berikut:',

    ],

    'bulkdelete' => [
        'error' => 'Tiada model dipilih, jadi tiada apa yang dipadamkan.',
        'success' => 'Model dipadam! | :success_count model dipadam!',
        'success_partial' => ':success_count model(s) telah dipadamkan, namun :fail_count tidak dapat dipadamkan kerana mereka masih mempunyai aset yang dikaitkan dengannya.',
    ],

];
