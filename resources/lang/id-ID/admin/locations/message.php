<?php

return array(

    'does_not_exist' => 'Lokasi tidak ada.',
    'assoc_users'    => 'This location is not currently deletable because it is the location of record for at least one item or user, has assets assigned to it, or is the parent location of another location. Please update your records to no longer reference this location and try again ',
    'assoc_assets'	 => 'Lokasi saat ini dikaitkan dengan setidaknya oleh satu aset dan tidak dapat dihapus. Perbarui aset Anda yang tidak ada referensi dari lokasi ini dan coba lagi. ',
    'assoc_child_loc'	 => 'Lokasi saat ini digunakan oleh induk salah satu dari turunan lokasi dan tidak dapat di hapus. Mohon perbarui lokasi Anda ke yang tidak ada referensi dengan lokasi ini dan coba kembali. ',
    'assigned_assets' => 'Aset yang Ditetapkan',
    'current_location' => 'Lokasi Saat Ini',
    'open_map' => 'Buka di Peta :map_provider_icon',
    'deleted_warning' => 'This location has been deleted. Please restore it before attempting to make any changes.',


    'create' => array(
        'error'   => 'Lokasi gagal di buat, mohon coba kebali.',
        'success' => 'Lokasi sukses di buat.'
    ),

    'update' => array(
        'error'   => 'Lokasi gagal di perbarui, mohon coba kembali',
        'success' => 'Lokasi sukses di perbarui.'
    ),

    'restore' => array(
        'error'   => 'Lokasi gagal dipulihkan, harap coba lagi',
        'success' => 'Lokasi berhasil dipulihkan.'
    ),

    'delete' => array(
        'confirm'   	=> 'Apakah Anda yakin untuk menghapus lokasi ini?',
        'error'   => 'Terdapat kesalahan pada saat penghapusan lokasi ini. Silahkan coba kembali.',
        'success' => 'Lokasi telah berhasil dihapus.'
    )

);
