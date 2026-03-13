<?php

return array(

    'accepted'                  => 'You have successfully accepted this item.',
    'declined'                  => 'You have successfully declined this item.',
    'bulk_manager_warn'	        => 'Pengguna anda sudah berhasil diperbarui, namun entri manajer anda tidak disimpan karena manajer yang anda pilih juga berada dalam daftar pengguna untuk disunting, dan pengguna mungkin bukan manajer mereka sendiri. Silahkan pilih pengguna anda lagi, tidak termasuk manajernya.',
    'user_exists'               => 'Pengguna sudah ada!',
    'cannot_delete'             => 'User does not exist or you do not have permission to delete them.',
    'user_not_found'            => 'User does not exist or you do not have permission to view them.',
    'user_login_required'       => 'Bidang masuk diperlukan',
    'user_has_no_assets_assigned' => 'No assets currently assigned to user.',
    'user_password_required'    => 'Kata sandi diperlukan.',
    'insufficient_permissions'  => 'Izin tidak cukup.',
    'user_deleted_warning'      => 'Pengguna ini sudah dihapus. Anda harus mengembalikan pengguna ini untuk menyuntingnya atau memberikannya aset baru.',
    'ldap_not_configured'        => 'Integrasi LDAP sudah tidak dikonfigurasikan untuk instalasi ini.',
    'password_resets_sent'      => 'Pengguna terpilih yang diaktifkan dan memiliki sebuah alamat email yang valid telah dikirimkan sebuah tautan pengaturan ulang kata sandi.',
    'password_reset_sent'       => 'A password reset link has been sent to :email!',
    'user_has_no_email'         => 'This user does not have an email address in their profile.',
    'log_record_not_found'        => 'A matching log record for this user could not be found.',


    'success' => array(
        'create'    => 'Pengguna berhasil dibuat.',
        'update'    => 'Pengguna berhasil diperbarui.',
        'update_bulk'    => 'Pengguna berhasil diperbarui!',
        'delete'    => 'Pengguna berhasil dihapus.',
        'ban'       => 'Pengguna berhasil diblokir.',
        'unban'     => 'Pengguna berhasil tidak diblokir.',
        'suspend'   => 'Pengguna berhasil ditangguhkan.',
        'unsuspend' => 'Pengguna berhasil tidak ditangguhkan.',
        'restored'  => 'Pengguna berhasil dikembalikan.',
        'import'    => 'Pengguna berhasil diimpor.',
    ),

    'error' => array(
        'create' => 'Terjadi masalah saat membuat pengguna. Silahkan coba lagi.',
        'update' => 'Terjadi masalah saat memperbarui pengguna. Silahkan coba lagi.',
        'delete' => 'Terjadi masalah saat menghapus pengguna. Silahkan coba lagi.',
        'delete_has_assets' => 'Pengguna ini memiliki item yang ditetapkan dan tidak dapat dihapus.',
        'delete_has_assets_var' => 'This user still has an asset assigned. Please check it in first.|This user still has :count assets assigned. Please check their assets in first.',
        'delete_has_licenses_var' => 'This user still has a license seats assigned. Please check it in first.|This user still has :count license seats assigned. Please check them in first.',
        'delete_has_accessories_var' => 'This user still has an accessory assigned. Please check it in first.|This user still has :count accessories assigned. Please check their assets in first.',
        'delete_has_locations_var' => 'This user still manages a location. Please select another manager first.|This user still manages :count locations. Please select another manager first.',
        'delete_has_users_var' => 'This user still manages another user. Please select another manager for that user first.|This user still manages :count users. Please select another manager for them first.',
        'unsuspend' => 'Terjadi masalah saat tidak menangguhkan pengguna. Silahkan coba lagi.',
        'import'    => 'Terjadi masalah saat mengimpor pengguna. Silahkan coba lagi.',
        'asset_already_accepted' => 'Aset ini sudah diterima.',
        'accept_or_decline' => 'Anda harus menerima atau menolak aset ini.',
        'cannot_delete_yourself' => 'We would feel really bad if you deleted yourself, please reconsider.',
        'incorrect_user_accepted' => 'Aset yang anda sudah coba terima tidak diperiksa untuk Anda.',
        'ldap_could_not_connect' => 'Tidak dapat terhubung ke peladen LDAP. Silahkan periksa konfigurasi peladen LDAP anda di berkas konfigurasi. <br>Kesalahan dari peladen LDAP:',
        'ldap_could_not_bind' => 'Tidak dapat mengikat peladen LDAP. Silakan periksa konfigurasi peladen LDAP Anda di berkas konfigurasi LDAP. <br>Kesalahan dari peladen LDAP: ',
        'ldap_could_not_search' => 'Tidak dapat mencari peladen LDAP. Silahkan periksa konfigurasi peladen LDAP anda di berkas konfigurasi LDAP.<br>Kesalahan dari peladen LDAP:',
        'ldap_could_not_get_entries' => 'Tidak bisa mendapatkan entri dari peladen LDAP. Silakan periksa konfigurasi peladen LDAP Anda di berkas konfigurasi LDAP. <br> Kesalahan dari peladen LDAP:',
        'password_ldap' => 'Kata sandi untuk akun ini dikelola oleh LDAP/Direktori Aktif. Silakan hubungi departemen IT Anda untuk mengubah kata sandi Anda. ',
        'multi_company_items_assigned' => 'This user has items assigned that belong to a different company. Please check them in or edit their company.'
    ),

    'deletefile' => array(
        'error'   => 'Berkas tidak terhapus. Silahkan coba lagi.',
        'success' => 'Berkas berhasil dihapus.',
    ),

    'upload' => array(
        'error'   => 'Berkas(s) tidak diunggah. Silahkan coba lagi.',
        'success' => 'Berkas(s) berhasil diunggah.',
        'nofiles' => 'Anda tidak memilih berkas apa pun untuk diunggah',
        'invalidfiles' => 'Satu atau lebih berkas anda terlalu besar atau jenis berkas tidak dibolehkan. Jenis berkas yang dibolehkan adalah png, gif, jpg, doc, docx, pdf, dan txt.',
    ),

    'inventorynotification' => array(
        'error'   => 'This user has no email set.',
        'success' => 'The user has been notified about their current inventory.'
    )
);