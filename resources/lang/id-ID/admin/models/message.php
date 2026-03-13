<?php

return array(

    'deleted' => 'Model aset yang dihapus',
    'does_not_exist' => 'Model tidak ada.',
    'no_association' => 'PERINGATAN! Model aset untuk item ini tidak valid atau hilang!',
    'no_association_fix' => 'Ini akan merusak banyak hal dengan cara yang aneh dan mengerikan. Edit aset ini sekarang untuk menetapkannya sebagai model.',
    'assoc_users'	 => 'Saat ini model tersebut terhubung dengan 1 atau lebih dengan aset dan tidak dapat di hapus. Silahkan hapus aset terlebih dahulu, kemudian coba hapus kembali. ',
    'invalid_category_type' => 'Kategori ini harus berupa kategori aset.',

    'create' => array(
        'error'   => 'Model gagal di buat, silahkan coba kembali.',
        'success' => 'Sukses mebuat model.',
        'duplicate_set' => 'Model aset dengan nomor nama, produsen dan model yang sama sudah ada.',
    ),

    'update' => array(
        'error'   => 'Model gagal diperbarui, silahkan coba kembali',
        'success' => 'Sukses memperbarui Model.',
    ),

    'delete' => array(
        'confirm'   => 'Anda yakin untuk menghapus model aset ini?',
        'error'   => 'Terdapat kesalahan pada saat penghapusan model. Silahkan coba kembali.',
        'success' => 'Model sukses terhapus.'
    ),

    'restore' => array(
        'error'   		=> 'Modal gagal di pulihkan, silahkan coba kembali',
        'success' 		=> 'Sukses memulihkan model.'
    ),

    'bulkedit' => array(
        'error'   		=> 'Tidak ada bidang yang berubah, jadi tidak ada yang diperbarui.',
        'success' 		=> 'Model berhasil diperbarui. |:model_count model berhasil diperbarui.',
        'warn'          => 'Anda akan memperbarui properti model berikut:|Anda akan mengedit properti dari :model_count model berikut:',

    ),

    'bulkdelete' => array(
        'error'   		    => 'Tidak ada model yang dipilih, jadi tidak ada yang dihapus.',
        'success' 		    => 'Model dihapus!|:success_count model dihapus!',
        'success_partial' 	=> ':success_count model telah dihapus, tetapi :fail_count tidak dapat dihapus karena masih memiliki aset yang terkait dengannya.'
    ),

);
