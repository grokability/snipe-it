<?php

return [

    'undeployable' 		 => 'The following assets cannot be deployed and have been removed from checkout: :asset_tags',
    'does_not_exist' 	 => 'Aset tidak ada.',
    'does_not_exist_var' => 'Aset dengan tag :asset_tag tidak ditemukan.',
    'no_tag' 	         => 'Tidak ada tag aset yang diberikan.',
    'does_not_exist_or_not_requestable' => 'Aset tersebut tidak ada atau tidak dapat di minta.',
    'assoc_users'	 	 => 'Aset ini sudah diberikan kepada pengguna dan tidak dapat di hapus. Silahkan cek aset terlebih dahulu kemudian coba hapus kembali. ',
    'warning_audit_date_mismatch' 	=> 'Tanggal audit berikutnya (:next_audit_date) untuk aset ini adalah sebelum tanggal audit terakhir (:last_audit_date). Harap perbarui tanggal audit berikutnya.',
    'labels_generated'   => 'Label berhasil dibuat.',
    'error_generating_labels' => 'Terjadi kesalahan saat membuat label.',
    'no_assets_selected' => 'Tidak ada aset yang dipilih.',

    'create' => [
        'error'   		=> 'Aset gagal di buat, silahkan coba kembali',
        'success' 		=> 'Sukses membuat aset',
        'success_linked' => 'Aset dengan tag :tag berhasil dibuat. <strong><a href=":link" style="color: white;">Klik di sini untuk melihat</a></strong>.',
        'multi_success_linked' => 'Aset dengan tag :links berhasil dibuat.|:count aset berhasil dibuat :links.',
        'partial_failure' => 'Aset gagal dibuat. Alasan: :failures|:count aset gagal dibuat. Alasan: :failures.',
        'target_not_found' => [
            'user' => 'The assigned user could not be found.',
            'asset' => 'The assigned asset could not be found.',
            'location' => 'The assigned location could not be found.',
        ],
    ],

    'update' => [
        'error'   			=> 'Gagal perbarui aset, silahkan coba kembali',
        'success' 			=> 'Sukses perbarui aset.',
        'encrypted_warning' => 'Aset berhasil diperbarui, tetapi kolom khusus yang terenkripsi tidak diperbarui karena izin',
        'nothing_updated'	=>  'Tidak ada kolom yang dipilih, jadi tidak ada yang diperbaharui.',
        'no_assets_selected'  =>  'Tidak ada aset yang dipilih, jadi tidak ada yang diperbarui.',
        'assets_do_not_exist_or_are_invalid' => 'Aset yang dipilih tidak dapat diperbarui.',
    ],

    'restore' => [
        'error'   		=> 'Aset gagal dikembalikan, silahkan coba lagi',
        'success' 		=> 'Aset berhasil dikembalikan.',
        'bulk_success' 		=> 'Aset berhasil dikembalikan.',
        'nothing_updated'   => 'Tidak ada aset yang dipilih, jadi tidak ada yang dipulihkan.', 
    ],

    'audit' => [
        'error'   		=> 'Audit aset tidak berhasil: :error.',
        'success' 		=> 'Audit aset berhasil login.',
    ],


    'deletefile' => [
        'error'   => 'Berkas tidak terhapus. Silahkan coba kembali.',
        'success' => 'Berkas berhasil dihapus.',
    ],

    'upload' => [
        'error'   => 'Berkas gagal diunggah. Silahkan coba kembali.',
        'success' => 'Berkas berhasil diunggah.',
        'nofiles' => 'Anda belum memilih berkas untuk diunggah, atau berkas yang akan diunggah terlalu besar',
        'invalidfiles' => 'Satu atau beberapa berkas Anda terlalu besar atau termasuk tipe berkas yang tidak diizinkan. Berkas yang diperbolehkan adalah png, gif, jpg, doc, docx, pdf, dan txt.',
    ],

    'import' => [
        'import_button'         => 'Proses Impor',
        'error'                 => 'Beberapa item tidak terimpor dengan benar.',
        'errorDetail'           => 'Item berikut tidak terimpor karena ada kesalahan.',
        'success'               => 'Berkas Anda berhasil terimpor',
        'file_delete_success'   => 'File anda telah berhasil dihapus',
        'file_delete_error'      => 'File tidak bisa dihapus',
        'file_missing' => 'File yang dipilih hilang',
        'file_already_deleted' => 'File yang dipilih telah dihapus',
        'header_row_has_malformed_characters' => 'Salah satu atau lebih atribut di baris header mengandung karakter UTF-8 yang tidak sah',
        'content_row_has_malformed_characters' => 'Salah satu atau lebih atribut di baris pertama konten mengandung karakter UTF-8 yang tidak sah',
        'transliterate_failure' => 'Transliteration from :encoding to UTF-8 failed due to invalid characters in input'
    ],


    'delete' => [
        'confirm'   	=> 'Apakah Anda yakin untuk menghapus aset ini?',
        'error'   		=> 'Terdapat kesalahan pada saat penghapusan aset. Silahkan coba kembali.',
        'assigned_to_error' => '{1}Asset Tag: :asset_tag is currently checked out. Check in this device before deletion.|[2,*]Asset Tags: :asset_tag are currently checked out. Check in these devices before deletion.',
        'nothing_updated'   => 'Tidak ada aset yang dipilih, jadi tidak ada yang dihapus.',
        'success' 		=> 'Aset sukses terhapus.',
    ],

    'checkout' => [
        'error'   		=> 'Aset gagal di berikan, silahkan coba kembali',
        'success' 		=> 'Sukses memberikan aset.',
        'user_does_not_exist' => 'Pengguna tersebut tidak terdaftar. Silahkan coba kembali.',
        'not_available' => 'Aset tersebut tidak tersedia untuk checkout!',
        'no_assets_selected' => 'Anda harus memilih setidaknya satu aset dari daftar',
    ],

    'multi-checkout' => [
        'error'   => 'Aset tidak dapat dipinjamkan, harap coba lagi.|Aset tidak dapat dipinjamkan, harap coba lagi',
        'success' => 'Aset berhasil dipinjamkan.|Aset berhasil dipinjamkan.',
    ],

    'checkin' => [
        'error'   		=> 'Aset gagal di terima, silahkan coba kembali',
        'success' 		=> 'Sukses menerima aset.',
        'user_does_not_exist' => 'Pengguna tersebut tidak terdaftar. Silahkan coba kembali.',
        'already_checked_in'  => 'Aset tersebut telah di terima.',

    ],

    'requests' => [
        'error'   		=> 'Request was not successful, please try again.',
        'success' 		=> 'Request successfully submitted.',
        'canceled'      => 'Request successfully canceled.',
        'cancel'        => 'Batalkan permintaan barang ini',
    ],

];
