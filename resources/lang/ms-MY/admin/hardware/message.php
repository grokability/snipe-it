<?php

return [

    'undeployable' 		 => 'The following assets cannot be deployed and have been removed from checkout: :asset_tags',
    'does_not_exist' 	 => 'Harta tidak wujud.',
    'does_not_exist_var' => 'Asset with tag :asset_tag not found.',
    'no_tag' 	         => 'No asset tag provided.',
    'does_not_exist_or_not_requestable' => 'That asset does not exist or is not requestable.',
    'assoc_users'	 	 => 'Harta ini sekarang telah diagihkan kepada pengguna dan tidak boleh dihapuskan. Sila semak status harta ini dahulu, dan kemudian cuba semula. ',
    'warning_audit_date_mismatch' 	=> 'This asset\'s next audit date (:next_audit_date) is before the last audit date (:last_audit_date). Please update the next audit date.',
    'labels_generated'   => 'Labels were successfully generated.',
    'error_generating_labels' => 'Error while generating labels.',
    'no_assets_selected' => 'No assets selected.',

    'create' => [
        'error'   		=> 'Harta gagal dicipta, sila cuba semula. :(',
        'success' 		=> 'Harta berjaya dicipta. :)',
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
        'error'   			=> 'Harta gagal dikemaskini, sila cuba semula',
        'success' 			=> 'Harta berjaya dikemaskini.',
        'encrypted_warning' => 'Asset updated successfully, but encrypted custom fields were not due to permissions',
        'nothing_updated'	=>  'Tiada medan dipilih, jadi tiada apa yang dikemas kini.',
        'no_assets_selected'  =>  'No assets were selected, so nothing was updated.',
        'assets_do_not_exist_or_are_invalid' => 'Selected assets cannot be updated.',
    ],

    'restore' => [
        'error'   		=> 'Aset tidak dipulihkan, sila cuba lagi',
        'success' 		=> 'Aset dipulihkan dengan jayanya.',
        'bulk_success' 		=> 'Aset dipulihkan dengan jayanya.',
        'nothing_updated'   => 'No assets were selected, so nothing was restored.', 
    ],

    'audit' => [
        'error'   		=> 'Asset audit unsuccessful: :error ',
        'success' 		=> 'Audit aset berjaya log.',
    ],


    'deletefile' => [
        'error'   => 'Fail tidak dipadam. Sila cuba lagi.',
        'success' => 'Fail berjaya dipadam.',
    ],

    'upload' => [
        'error'   => 'Fail tidak dimuat naik. Sila cuba lagi.',
        'success' => 'Fail berjaya dimuat naik.',
        'nofiles' => 'Anda tidak memilih sebarang fail untuk dimuat naik, atau fail yang anda cuba muat naik terlalu besar',
        'invalidfiles' => 'Satu atau lebih daripada fail anda terlalu besar atau merupakan filetype yang tidak dibenarkan. Filetype yang dibenarkan adalah png, gif, jpg, doc, docx, pdf, dan txt.',
    ],

    'import' => [
        'import_button'         => 'Process Import',
        'error'                 => 'Sesetengah item tidak diimport dengan betul.',
        'errorDetail'           => 'Item berikut tidak diimport kerana kesilapan.',
        'success'               => 'Fail anda telah diimport',
        'file_delete_success'   => 'Fail anda telah berjaya dihapuskan',
        'file_delete_error'      => 'Fail tidak dapat dipadamkan',
        'file_missing' => 'The file selected is missing',
        'file_already_deleted' => 'The file selected was already deleted',
        'header_row_has_malformed_characters' => 'One or more attributes in the header row contain malformed UTF-8 characters',
        'content_row_has_malformed_characters' => 'One or more attributes in the first row of content contain malformed UTF-8 characters',
        'transliterate_failure' => 'Transliteration from :encoding to UTF-8 failed due to invalid characters in input'
    ],


    'delete' => [
        'confirm'   	=> 'Anda pasti anda ingin hapuskan harta ini?',
        'error'   		=> 'Ada isu semasa menghapuskan harta. Sila cuba lagi.',
        'assigned_to_error' => '{1}Asset Tag: :asset_tag is currently checked out. Check in this device before deletion.|[2,*]Asset Tags: :asset_tag are currently checked out. Check in these devices before deletion.',
        'nothing_updated'   => 'Tiada aset dipilih, jadi tiada apa yang dipadamkan.',
        'success' 		=> 'Harta berjaya dihapuskan.',
    ],

    'checkout' => [
        'error'   		=> 'Harta gagal diagihkan, sila cuba semula',
        'success' 		=> 'Harta berjaya diagihkan.',
        'user_does_not_exist' => 'Pengguna tak sah. Sila cuba lagi.',
        'not_available' => 'Aset itu tidak tersedia untuk checkout!',
        'no_assets_selected' => 'Anda mesti memilih sekurang-kurangnya satu aset dari senarai',
    ],

    'multi-checkout' => [
        'error'   => 'Asset was not checked out, please try again|Assets were not checked out, please try again',
        'success' => 'Asset checked out successfully.|Assets checked out successfully.',
    ],

    'checkin' => [
        'error'   		=> 'Harta tidak diterima, sila cuba lagi',
        'success' 		=> 'Harta berjaya diterima.',
        'user_does_not_exist' => 'Pengguna tidah sah. Sila cuba lagi.',
        'already_checked_in'  => 'Aset itu sudah diperiksa.',

    ],

    'requests' => [
        'error'   		=> 'Request was not successful, please try again.',
        'success' 		=> 'Request successfully submitted.',
        'canceled'      => 'Request successfully canceled.',
        'cancel'        => 'Cancel this item request',
    ],

];
