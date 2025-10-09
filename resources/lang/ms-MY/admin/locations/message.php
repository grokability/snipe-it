<?php

return array(

    'does_not_exist' => 'Lokasi tidak wujud.',
    'assoc_users'    => 'This location is not currently deletable because it is the location of record for at least one item or user, has assets assigned to it, or is the parent location of another location. Please update your records to no longer reference this location and try again ',
    'assoc_assets'	 => 'Lokasi ini kini dikaitkan dengan sekurang-kurangnya satu aset dan tidak boleh dihapuskan. Sila kemas kini aset anda untuk tidak merujuk lagi lokasi ini dan cuba lagi.',
    'assoc_child_loc'	 => 'Lokasi ini adalah ibu bapa sekurang-kurangnya satu lokasi kanak-kanak dan tidak boleh dipadamkan. Sila kemas kini lokasi anda untuk tidak merujuk lokasi ini lagi dan cuba lagi.',
    'assigned_assets' => 'Aset yang Ditetapkan',
    'current_location' => 'Lokasi Semasa',
    'open_map' => 'Buka dalam :map_provider_icon Peta',
    'deleted_warning' => 'This location has been deleted. Please restore it before attempting to make any changes.',


    'create' => array(
        'error'   => 'Lokasi gagal dicipta, sila cuba lagi.',
        'success' => 'Lokasi berjaya dicipta.'
    ),

    'update' => array(
        'error'   => 'Lokasi gagal dikemaskini, sila cuba lagi',
        'success' => 'Lokasi berjaya dikemaskini.'
    ),

    'restore' => array(
        'error'   => 'Lokasi tidak dipulihkan, sila cuba semula.',
        'success' => 'Lokasi berjaya dipulihkan.'
    ),

    'delete' => array(
        'confirm'   	=> 'Anda pasti and ingin menghapuskan lokasi ini?',
        'error'   => 'Ada isu semasa menghapuskan lokasi. Sila cuba lagi.',
        'success' => 'Lokasi berjaya dihapuskan.'
    )

);
