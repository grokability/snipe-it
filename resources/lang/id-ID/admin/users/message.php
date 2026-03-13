<?php

return array(

    'accepted'                  => 'You have successfully accepted this item.',
    'declined'                  => 'You have successfully declined this item.',
    'bulk_manager_warn'	        => 'Pengguna Anda telah berhasil diperbarui, namun entri pengelola Anda tidak disimpan karena manajer yang Anda pilih juga ada dalam daftar pengguna untuk diedit, dan pengguna mungkin bukan manajer mereka sendiri. Silakan pilih pengguna Anda lagi, tidak termasuk manajernya.',
    'user_exists'               => 'Pengguna sudah ada!',
    'cannot_delete'             => 'User does not exist or you do not have permission to delete them.',
    'user_not_found'            => 'Pengguna tidak ada atau Anda tidak memiliki izin untuk melihatnya.',
    'user_login_required'       => 'Kolom login wajib di-isi',
    'user_has_no_assets_assigned' => 'Tidak ada aset yang saat ini ditetapkan kepada pengguna.',
    'user_password_required'    => 'Kata sandi wajib di-isi.',
    'insufficient_permissions'  => 'Tidak ada hak akses.',
    'user_deleted_warning'      => 'Pengguna ini telah di hapus. Anda harus kembalikan dahulu pengguna ini jika ingin menyunting atau di berikan hak kelola aset.',
    'ldap_not_configured'        => 'Integrasi LDAP belum dikonfigurasi untuk instalasi ini.',
    'password_resets_sent'      => 'Pengguna terpilih yang diaktifkan dan memiliki alamat email yang valid telah dikirimi tautan pengaturan ulang kata sandi.',
    'password_reset_sent'       => 'Tautan reset kata sandi telah dikirim ke :email!',
    'user_has_no_email'         => 'Pengguna ini tidak memiliki alamat email di profil mereka.',
    'log_record_not_found'        => 'Catatan log yang cocok untuk pengguna ini tidak dapat ditemukan.',


    'success' => array(
        'create'    => 'Pengguna sukses di buat.',
        'update'    => 'Pengguna sukses di perbarui.',
        'update_bulk'    => 'Pengguna berhasil diperbarui!',
        'delete'    => 'Pengguna sukses di hapus.',
        'ban'       => 'Pengguna sukses di blokir.',
        'unban'     => 'Pengguna sukses tidak di blokir.',
        'suspend'   => 'Pengguna sukses di tangguhkan.',
        'unsuspend' => 'Pengguna sukses tidak di tangguhkan.',
        'restored'  => 'Pengguna sukses di kembalikan.',
        'import'    => 'Sukses mengimpor pengguna.',
    ),

    'error' => array(
        'create' => 'Terdapat kesalahan ketika membuat pengguna. Silahkan coba kembali.',
        'update' => 'Terdapat masalah ketika memperbarui pengguna. Silahkan coba kembali.',
        'delete' => 'Terdapat masalah ketika menghapus pengguna. Silahkan coba kembali.',
        'delete_has_assets' => 'Pengguna ini memiliki item yang ditugaskan dan tidak dapat dihapus.',
        'delete_has_assets_var' => 'Pengguna ini masih memiliki satu aset yang ditetapkan. Harap kembalikan aset tersebut terlebih dahulu.|Pengguna ini masih memiliki :count aset yang ditugaskan. Harap kembalikan aset-aset tersebut terlebih dahulu.',
        'delete_has_licenses_var' => 'Pengguna ini masih memiliki satu lisensi yang ditetapkan. Harap kembalikan lisensi tersebut terlebih dahulu.|Pengguna ini masih memiliki :count lisensi yang ditugaskan. Harap kembalikan lisensi-lisensi tersebut terlebih dahulu.',
        'delete_has_accessories_var' => 'Pengguna ini masih memiliki satu aksesori yang ditugaskan. Harap kembalikan aksesori tersebut terlebih dahulu.| Pengguna ini masih memiliki :count aksesori yang ditugaskan. Harap kembalikan aksesori-aksesori tersebut terlebih dahulu.',
        'delete_has_locations_var' => 'Pengguna ini masih mengelola satu lokasi. Harap pilih pengelola lain terlebih dahulu.|Pengguna ini masih mengelola :count lokasi. Harap pilih pengelola lain terlebih dahulu.',
        'delete_has_users_var' => 'Pengguna ini masih mengelola pengguna lain. Harap pilih pengelola lain untuk pengguna tersebut terlebih dahulu.| Pengguna ini masih mengelola :count pengguna. Harap pilih pengelola lain untuk mereka terlebih dahulu.',
        'unsuspend' => 'Terdapat masalah ketika menangguhkan pengguna. Silahkan coba kembali.',
        'import'    => 'Terdapat masalah ketika mengimpor pengguna. Silahkan coba kembali.',
        'asset_already_accepted' => 'Aset ini telah di terima.',
        'accept_or_decline' => 'Anda harus menolak atau menerima aset ini.',
        'cannot_delete_yourself' => 'Kami akan sangat kecewa jika Anda menghapus diri Anda sendiri, harap pertimbangkan kembali. ',
        'incorrect_user_accepted' => 'Aset yang akan di berikan ke anda, belum di setujui.',
        'ldap_could_not_connect' => 'Gagal koneksi ke server LDAP. Silahkan periksa konfigurasi server LDAP di berkas config. <br>Eror dari server LDAP:',
        'ldap_could_not_bind' => 'Server LDAP gagal mengikat. Silahkan cek kembali konfigurasi server LDAP di berkas config. <br>Eror dari server LDAP: ',
        'ldap_could_not_search' => 'Gagal mencari server LDAP. Silahkan cek konfigurasi server LDAP di berkas config LDAP. <br>Eror dari server LDAP:',
        'ldap_could_not_get_entries' => 'Gagal menerima catatan dari server LDAP. Silahkan cek konfigurasi server LDAP di berkas config LDAP. <br>Eror dari server LDAP:',
        'password_ldap' => 'Kata sandi untuk akun ini dikelola oleh LDAP / Active Directory. Silakan menghubungi departemen TI Anda untuk mengganti kata sandi Anda.',
        'multi_company_items_assigned' => 'Pengguna ini memiliki item yang ditetapkan yang dimiliki oleh company lain. Harap kembalikan item tersebut atau edit entitas mereka.'
    ),

    'deletefile' => array(
        'error'   => 'Berkas belum terhapus. Silahkan coba kembali.',
        'success' => 'Berkas sukses di hapus.',
    ),

    'upload' => array(
        'error'   => 'Berkas belum terunggah. Silakan coba kembali.',
        'success' => 'Berkas sukses terunggah.',
        'nofiles' => 'Anda belum memilih berkas untuk di unggah',
        'invalidfiles' => 'Satu atau lebih dari file Anda terlalu besar atau jenis berkas yang tidak diperbolehkan. Tipe file diizinkan adalah png, gif, jpg, doc, docx, pdf, dan txt.',
    ),

    'inventorynotification' => array(
        'error'   => 'Pengguna ini belum mengatur email.',
        'success' => 'Pengguna telah diberi tahu tentang inventaris mereka saat ini.'
    )
);