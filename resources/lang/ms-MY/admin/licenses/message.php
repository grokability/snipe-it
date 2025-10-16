<?php

return array(

    'does_not_exist' => 'License does not exist or you do not have permission to view it.',
    'user_does_not_exist' => 'User does not exist or you do not have permission to view them.',
    'asset_does_not_exist' 	=> 'Harta yang anda ingin sekutukan dengan lesen tidak wujud.',
    'owner_doesnt_match_asset' => 'Harta yang anda ingin sekutukan dengan lesen ini dimiliki oleh orang lain selain dari orang yang dipilih dalam pilihan yang disekutukan.',
    'assoc_users'	 => 'Lesen ini telah diagihkan kepada pengguna dan tidak boleh dihapuskan. Sila semak lesen terlebih dahulu, dan cuba hapus semula.  ',
    'select_asset_or_person' => 'Anda mesti memilih aset atau pengguna, tetapi tidak kedua-duanya.',
    'not_found' => 'Lesen tidak ditemui',
    'seats_available' => ':seat_count seats available',


    'create' => array(
        'error'   => 'Lesen gagal dicipta, sila cuba lagi.',
        'success' => 'Lesen berjaya dicipta.'
    ),

    'deletefile' => array(
        'error'   => 'Fail tidak dipadam. Sila cuba lagi.',
        'success' => 'Fail berjaya dipadam.',
    ),

    'upload' => array(
        'error'   => 'Fail tidak dimuat naik. Sila cuba lagi.',
        'success' => 'Fail berjaya dimuat naik.',
        'nofiles' => 'Anda tidak memilih sebarang fail untuk dimuat naik, atau fail yang anda cuba muat naik terlalu besar',
        'invalidfiles' => 'Satu atau lebih daripada fail anda terlalu besar atau merupakan filetype yang tidak dibenarkan. Filetype yang dibenarkan adalah png, gif, jpg, jpeg, doc, docx, pdf, txt, zip, rar, rtf, xml, dan lic.',
    ),

    'update' => array(
        'error'   => 'Lesen gagal dikemaskini, sila cuba lagi',
        'success' => 'Lesen berjaya dikemaskini.'
    ),

    'delete' => array(
        'confirm'   => 'Anda pasti and ingin menghapuskan lesen ini?',
        'error'   => 'Ada isu semada menghapuskan lesen, sila cuba lagi.',
        'success' => 'Lesen berjaya dihapuskan.'
    ),

    'checkout' => array(
        'error'   => 'Ada isu semasa agihan lesen. Sila cuba lagi.',
        'success' => 'Lesen berjaya diagihkan',
        'not_enough_seats' => 'Not enough license seats available for checkout',
        'mismatch' => 'The license seat provided does not match the license',
        'unavailable' => 'This seat is not available for checkout.',
        'license_is_inactive' => 'This license is expired or terminated.',
    ),

    'checkin' => array(
        'error'   => 'Ada isu semasa terima lesen. Sila cuba lagi.',
        'not_reassignable' => 'Seat has been used',
        'success' => 'Lesen berjaya diterima'
    ),

);
