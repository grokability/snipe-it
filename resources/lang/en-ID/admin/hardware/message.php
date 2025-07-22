<?php

return [

    'undeployable' 		 => 'The following assets cannot be deployed and have been removed from checkout: :asset_tags',
    'does_not_exist' 	 => 'Aset tidak ada.',
    'does_not_exist_var' => 'Asset with tag :asset_tag not found.',
    'no_tag' 	         => 'No asset tag provided.',
    'does_not_exist_or_not_requestable' => 'That asset does not exist or is not requestable.',
    'assoc_users'	 	 => 'Aset ini saat ini diperiksa ke pengguna dan tidak dapat dihapus. Harap periksa dulu asetnya, lalu coba hapus lagi. ',
    'warning_audit_date_mismatch' 	=> 'This asset\'s next audit date (:next_audit_date) is before the last audit date (:last_audit_date). Please update the next audit date.',
    'labels_generated'   => 'Labels were successfully generated.',
    'error_generating_labels' => 'Error while generating labels.',
    'no_assets_selected' => 'No assets selected.',

    'create' => [
        'error'   		=> 'Aset tidak dibuat, coba lagi. :(',
        'success' 		=> 'Aset berhasil dibuat. :)',
        'success_linked' => 'Asset with tag :tag was created successfully. <strong><a href=":link" style="color: white;">Click here to view</a></strong>.',
        'multi_success_linked' => 'Asset with tag :links was created successfully.|:count assets were created succesfully. :links.',
        'partial_failure' => 'An asset was unable to be created. Reason: :failures|:count assets were unable to be created. Reasons: :failures',
        'target_not_found' => [
            'user' => 'The assigned user could not be found.',
            'asset' => 'The assigned asset could not be found.',
            'location' => 'The assigned location could not be found.',
        ],
    ],

    'update' => [
        'error'   			=> 'Aset tidak diperbarui, coba lagi',
        'success' 			=> 'Aset Berhasil diperbarui.',
        'encrypted_warning' => 'Asset updated successfully, but encrypted custom fields were not due to permissions',
        'nothing_updated'	=>  'Tidak ada kategori yang dipilih, jadi tidak ada yang diperbarui.',
        'no_assets_selected'  =>  'No assets were selected, so nothing was updated.',
        'assets_do_not_exist_or_are_invalid' => 'Selected assets cannot be updated.',
    ],

    'restore' => [
        'error'   		=> 'Aset tidak dikembalikan, coba lagi',
        'success' 		=> 'Aset Berhasil dikembalikan.',
        'bulk_success' 		=> 'Aset Berhasil dikembalikan.',
        'nothing_updated'   => 'No assets were selected, so nothing was restored.', 
    ],

    'audit' => [
        'error'   		=> 'Asset audit unsuccessful: :error ',
        'success' 		=> 'Audit aset berhasil dimasuki.',
    ],


    'deletefile' => [
        'error'   => 'Berkas tidak terhapus. Silahkan coba lagi.',
        'success' => 'File berhasil dihapus.',
    ],

    'upload' => [
        'error'   => 'Berkas(s) tidak diunggah. Silahkan coba lagi.',
        'success' => 'Berkas(s) berhasil diunggah.',
        'nofiles' => 'Anda tidak memilih file untuk diunggah, atau file yang ingin Anda unggah terlalu besar',
        'invalidfiles' => 'Satu atau lebih berkas anda terlalu besar atau jenis berkas tidak dibolehkan. Jenis berkas yang dibolehkan adalah png, gif, jpg, doc, docx, pdf, dan txt.',
    ],

    'import' => [
        'import_button'         => 'Process Import',
        'error'                 => 'Beberapa item tidak diimpor dengan benar.',
        'errorDetail'           => 'Item berikut tidak diimpor karena kesalahan.',
        'success'               => 'File Anda telah diimpor',
        'file_delete_success'   => 'File anda telah berhasil dihapus',
        'file_delete_error'      => 'File tidak dapat dihapus',
        'file_missing' => 'The file selected is missing',
        'file_already_deleted' => 'The file selected was already deleted',
        'header_row_has_malformed_characters' => 'One or more attributes in the header row contain malformed UTF-8 characters',
        'content_row_has_malformed_characters' => 'One or more attributes in the first row of content contain malformed UTF-8 characters',
        'transliterate_failure' => 'Transliteration from :encoding to UTF-8 failed due to invalid characters in input'
    ],


    'delete' => [
        'confirm'   	=> 'Yakin ingin menghapus aset ini?',
        'error'   		=> 'Terjadi masalah saat menghapus aset. Silahkan coba lagi.',
        'assigned_to_error' => '{1}Asset Tag: :asset_tag is currently checked out. Check in this device before deletion.|[2,*]Asset Tags: :asset_tag are currently checked out. Check in these devices before deletion.',
        'nothing_updated'   => 'Tidak ada aset yang dipilih, jadi tidak ada yang diperbarui.',
        'success' 		=> 'Aset berhasil dihapus.',
    ],

    'checkout' => [
        'error'   		=> 'Aset tidak dapat diperiksa, silahkan coba lagi',
        'success' 		=> 'Aset berhasil diperiksa.',
        'user_does_not_exist' => 'Pengguna tidak cocok. Silahkan coba lagi.',
        'not_available' => 'Aset itu tersebut tidak tersedia untuk checkout!',
        'no_assets_selected' => 'Anda harus memilih setidaknya satu aset dari daftar',
    ],

    'multi-checkout' => [
        'error'   => 'Asset was not checked out, please try again|Assets were not checked out, please try again',
        'success' => 'Asset checked out successfully.|Assets checked out successfully.',
    ],

    'checkin' => [
        'error'   		=> 'Aset tidak dicek, coba lagi',
        'success' 		=> 'Aset berhasil dicek.',
        'user_does_not_exist' => 'Pengguna tidak cocok. Silahkan coba lagi.',
        'already_checked_in'  => 'Aset tersebut sudah diperiksa.',

    ],

    'requests' => [
        'error'   		=> 'Request was not successful, please try again.',
        'success' 		=> 'Request successfully submitted.',
        'canceled'      => 'Request successfully canceled.',
        'cancel'        => 'Cancel this item request',
    ],

];
