<?php

return array(

    'accepted'                  => 'You have successfully accepted this item.',
    'declined'                  => 'You have successfully declined this item.',
    'bulk_manager_warn'	        => 'Pengguna anda telah berjaya dikemas kini, namun entri pengurus anda tidak disimpan kerana pengurus yang anda pilih juga dalam senarai pengguna untuk diedit, dan pengguna mungkin bukan pengurus mereka sendiri. Sila pilih pengguna anda sekali lagi, tidak termasuk pengurus.',
    'user_exists'               => 'Pengguna telah wujud!',
    'cannot_delete'             => 'User does not exist or you do not have permission to delete them.',
    'user_not_found'            => 'User does not exist or you do not have permission to view them.',
    'user_login_required'       => 'Ruangan log masuk diperlukan',
    'user_has_no_assets_assigned' => 'No assets currently assigned to user.',
    'user_password_required'    => 'Ruangan kata kunci diperlukan.',
    'insufficient_permissions'  => 'Tidak cukup kuasa.',
    'user_deleted_warning'      => 'Pengguna telah dihapuskan. Anda perlu masukkan semula pengguna ini untuk kemaskini atau untuk serahkan dia harta baru.',
    'ldap_not_configured'        => 'Integrasi LDAP belum dikonfigurasi untuk pemasangan ini.',
    'password_resets_sent'      => 'The selected users who are activated and have a valid email addresses have been sent a password reset link.',
    'password_reset_sent'       => 'A password reset link has been sent to :email!',
    'user_has_no_email'         => 'This user does not have an email address in their profile.',
    'log_record_not_found'        => 'A matching log record for this user could not be found.',


    'success' => array(
        'create'    => 'Pengguna berjaya dicipta.',
        'update'    => 'Pengguna berjaya dikemaskini.',
        'update_bulk'    => 'Pengguna berjaya dikemas kini!',
        'delete'    => 'Pnegguna berjaya dihapuskan.',
        'ban'       => 'Pengguna berjaya disekat.',
        'unban'     => 'Pengguna berjaya dibernarkan.',
        'suspend'   => 'Pengguna berjaya digantung.',
        'unsuspend' => 'Pengguna berjaya dilepaskan.',
        'restored'  => 'Pengguna berjaya dimasukkan semula.',
        'import'    => 'Pengguna diimport dengan jayanya.',
    ),

    'error' => array(
        'create' => 'Ada isu semasa mencipta pengguna. Sila cuba lagi.',
        'update' => 'Ada isu semasa mencipta pengguna. Sila cuba lagi.',
        'delete' => 'Ada isu semasa menghapuskan pengguna. Sila cuba lagi.',
        'delete_has_assets' => 'Pengguna ini mempunyai item yang ditetapkan dan tidak dapat dipadamkan.',
        'delete_has_assets_var' => 'This user still has an asset assigned. Please check it in first.|This user still has :count assets assigned. Please check their assets in first.',
        'delete_has_licenses_var' => 'This user still has a license seats assigned. Please check it in first.|This user still has :count license seats assigned. Please check them in first.',
        'delete_has_accessories_var' => 'This user still has an accessory assigned. Please check it in first.|This user still has :count accessories assigned. Please check their assets in first.',
        'delete_has_locations_var' => 'This user still manages a location. Please select another manager first.|This user still manages :count locations. Please select another manager first.',
        'delete_has_users_var' => 'This user still manages another user. Please select another manager for that user first.|This user still manages :count users. Please select another manager for them first.',
        'unsuspend' => 'Ada isu semasa melepakan pengguna. Sila cuba lagi. ',
        'import'    => 'Terdapat masalah mengimport pengguna. Sila cuba lagi.',
        'asset_already_accepted' => 'Aset ini telah diterima.',
        'accept_or_decline' => 'Anda mesti menerima atau menolak aset ini.',
        'cannot_delete_yourself' => 'We would feel really bad if you deleted yourself, please reconsider.',
        'incorrect_user_accepted' => 'Aset yang anda telah cuba terima tidak diperiksa kepada anda.',
        'ldap_could_not_connect' => 'Tidak dapat menyambung ke pelayan LDAP. Sila periksa konfigurasi pelayan LDAP anda dalam fail konfigurasi LDAP. <br>Error dari LDAP Server:',
        'ldap_could_not_bind' => 'Tidak dapat mengikat pelayan LDAP. Sila periksa konfigurasi pelayan LDAP anda dalam fail konfigurasi LDAP. <br>Error dari LDAP Server:',
        'ldap_could_not_search' => 'Tidak dapat mencari pelayan LDAP. Sila periksa konfigurasi pelayan LDAP anda dalam fail konfigurasi LDAP. <br>Error dari LDAP Server:',
        'ldap_could_not_get_entries' => 'Tidak dapat masuk dari pelayan LDAP. Sila periksa konfigurasi pelayan LDAP anda dalam fail konfigurasi LDAP. <br>Error dari LDAP Server:',
        'password_ldap' => 'Kata laluan untuk akaun ini diuruskan oleh LDAP / Active Directory. Sila hubungi jabatan IT anda untuk menukar kata laluan anda.',
        'multi_company_items_assigned' => 'This user has items assigned that belong to a different company. Please check them in or edit their company.'
    ),

    'deletefile' => array(
        'error'   => 'Fail tidak dipadam. Sila cuba lagi.',
        'success' => 'Fail berjaya dipadam.',
    ),

    'upload' => array(
        'error'   => 'Fail tidak dimuat naik. Sila cuba lagi.',
        'success' => 'Fail berjaya dimuat naik.',
        'nofiles' => 'Anda tidak memilih sebarang fail untuk dimuat naik',
        'invalidfiles' => 'Satu atau lebih daripada fail anda terlalu besar atau merupakan filetype yang tidak dibenarkan. Filetype yang dibenarkan adalah png, gif, jpg, doc, docx, pdf, dan txt.',
    ),

    'inventorynotification' => array(
        'error'   => 'This user has no email set.',
        'success' => 'The user has been notified about their current inventory.'
    )
);