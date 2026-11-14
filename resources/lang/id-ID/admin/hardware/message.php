<?php

return [

    'undeployable' => 'Aset berikut tidak dapat digunakan dan telah dihapus dari checkout: :asset_tags',
    'does_not_exist' => 'Aset tidak ada.',
    'does_not_exist_var' => 'Aset dengan tag :asset_tag tidak ditemukan.',
    'no_tag' => 'Tidak ada tag aset yang diberikan.',
    'does_not_exist_or_not_requestable' => 'Aset tersebut tidak ada atau tidak dapat di minta.',
    'assoc_users' => 'Aset ini sudah diberikan kepada pengguna dan tidak dapat di hapus. Silahkan cek aset terlebih dahulu kemudian coba hapus kembali. ',
    'warning_audit_date_mismatch' => 'Tanggal audit berikutnya (:next_audit_date) untuk aset ini adalah sebelum tanggal audit terakhir (:last_audit_date). Harap perbarui tanggal audit berikutnya.',
    'labels_generated' => 'Label berhasil dibuat.',
    'error_generating_labels' => 'Terjadi kesalahan saat membuat label.',
    'no_assets_selected' => 'Tidak ada aset yang dipilih.',

    'create' => [
        'error' => 'Aset gagal di buat, silahkan coba kembali',
        'success' => 'Sukses membuat aset',
        'success_no_checkout' => 'Asset created successfully, but was not checked out because you do not have permission to check assets out.',
        'checkout_skipped_no_permission' => 'The asset was created, but was not checked out to the requested target because you do not have permission to check assets out.',
        'success_linked' => 'Aset dengan tag :tag berhasil dibuat. <strong><a href=":link" style="color: white;">Klik di sini untuk melihat</a></strong>.',
        'multi_success_linked' => 'Aset dengan tag :links berhasil dibuat.|:count aset berhasil dibuat :links.',
        'partial_failure' => 'Aset gagal dibuat. Alasan: :failures|:count aset gagal dibuat. Alasan: :failures.',
        'target_not_found' => [
            'user' => 'Pengguna yang ditugaskan tidak ditemukan.',
            'asset' => 'Aset yang diperuntukkan tidak ditemukan.',
            'location' => 'The assigned location could not be found.',
        ],
    ],

    'update' => [
        'error' => 'Gagal perbarui aset, silahkan coba kembali',
        'success' => 'Sukses perbarui aset.',
        'encrypted_warning' => 'Aset berhasil diperbarui, tetapi kolom khusus yang terenkripsi tidak diperbarui karena izin',
        'nothing_updated' => 'Tidak ada kolom yang dipilih, jadi tidak ada yang diperbaharui.',
        'no_assets_selected' => 'Tidak ada aset yang dipilih, jadi tidak ada yang diperbarui.',
        'assets_do_not_exist_or_are_invalid' => 'Aset yang dipilih tidak dapat diperbarui.',
    ],

    'bulk_update' => [
        'success' => 'Asset updated successfully.|:count assets were updated successfully.',
        'partial' => ':success asset(s) updated successfully, :failed failed. See the results array for details.',
        'error' => 'No assets were updated. See the results array for details.',
    ],

    'restore' => [
        'error' => 'Aset gagal dikembalikan, silahkan coba lagi',
        'success' => 'Aset berhasil dikembalikan.',
        'bulk_success' => 'Aset berhasil dikembalikan.',
        'nothing_updated' => 'Tidak ada aset yang dipilih, jadi tidak ada yang dipulihkan.',
    ],

    'audit' => [
        'error' => 'Audit aset tidak berhasil: :error.',
        'success' => 'Audit aset berhasil login.',
    ],

    'deletefile' => [
        'error' => 'Berkas tidak terhapus. Silahkan coba kembali.',
        'success' => 'Berkas berhasil dihapus.',
    ],

    'upload' => [
        'error' => 'Berkas gagal diunggah. Silahkan coba kembali.',
        'success' => 'Berkas berhasil diunggah.',
        'nofiles' => 'Anda belum memilih berkas untuk diunggah, atau berkas yang akan diunggah terlalu besar',
        'invalidfiles' => 'Satu atau beberapa berkas Anda terlalu besar atau termasuk tipe berkas yang tidak diizinkan. Berkas yang diperbolehkan adalah png, gif, jpg, doc, docx, pdf, dan txt.',
    ],

    'import' => [
        'import_button' => 'Proses Impor',
        'error' => 'Beberapa item tidak terimpor dengan benar.',
        'errorDetail' => 'Item berikut tidak terimpor karena ada kesalahan.',
        'success' => 'Berkas Anda berhasil terimpor',
        'file_delete_success' => 'File anda telah berhasil dihapus',
        'file_delete_error' => 'File tidak bisa dihapus',
        'file_missing' => 'File yang dipilih hilang',
        'file_already_deleted' => 'File yang dipilih telah dihapus',
        'file_missing_on_disk' => 'The file for this import is no longer on disk. It may have been deleted outside of Snipe-IT. Delete this entry and re-upload the file to try again.',
        'file_empty' => 'This file has no data rows. Nothing can be imported from it.',
        'already_processing' => 'This import is currently being processed by another user. Please wait for it to finish before trying again.',
        'header_row_missing' => 'This file does not have a recognized header row. Delete this entry and re-upload the file to try again.',
        'header_row_has_malformed_characters' => 'Salah satu atau lebih atribut di baris header mengandung karakter UTF-8 yang tidak sah',
        'content_row_has_malformed_characters' => 'Salah satu atau lebih atribut di baris pertama konten mengandung karakter UTF-8 yang tidak sah',
        'transliterate_failure' => 'Transliterasi dari :encoding ke UTF-8 gagal karena karakter input tidak valid',
        'bulk_delete' => [
            'button' => 'Delete Selected (:count)',
            'confirm_title' => 'Delete selected import files?',
            'confirm_body' => 'You are about to permanently delete :count import file(s). This cannot be undone.',
            'confirm_button' => 'Hapus',
            'success' => 'Import file deleted successfully.|:count import files were deleted successfully.',
            'skipped' => ':count file(s) were skipped because you do not have permission to delete them.',
            'select_all' => 'Select all files on this page',
            'select_row' => 'Select :file for bulk delete',
        ],
        'row_count' => '{0} No data rows in this file|{1} :count data row to import|[2,*] :count data rows to import',
        'summary' => [
            'created' => 'Dibuat',
            'updated' => 'Updated',
            'skipped' => 'Skipped as duplicates',
            'errored' => 'Errored',
            'no_changes' => 'The import finished but nothing was created or updated. Every row was skipped, usually because the underlying records already existed. Check the counts below and adjust the CSV or import type if that is not what you expected.',
        ],
        'type_required' => 'Please select an import type before continuing.',
        'processing' => 'Processing your import. Please wait until this finishes before closing the page.',
        'backup_running' => 'Running backup before importing. This can take a while on larger files. Please wait.',
        'backup_label' => 'Pre-import backup',
        'backup_complete' => 'Backup complete',
        'import_label' => 'Impor',
        'required_fields_missing' => 'The following required fields are not mapped: :fields',
        'history' => [
            'missing_asset_tag_identity' => '(missing asset tag)',
            'missing_asset_tag_message' => 'Row skipped: no asset tag provided.',
            'asset_not_found_message' => 'Asset with this tag does not exist. Import assets first, then re-run the history import.',
            'target_not_matched_message' => 'No :target_type matched ":name". For users, toggle the match-by options in step 1 or create the user first. For locations, make sure the CSV location name matches an existing location exactly.',
            'invalid_target_type_message' => 'Target Type ":value" is not recognized. Use "user" or "location", or leave the column blank to default to user.',
        ],
        'wizard' => [
            'step_type' => 'Choose type',
            'step_map' => 'Map fields',
            'step_preview' => 'Pratinjau',
            'back' => 'Kembali',
            'next' => 'Berikutnya',
            'preview_button' => 'Pratinjau',
            'process' => 'Process import',
            'preview_intro' => 'Previewing the first :count row(s) after applying your mapping. Use the Back button if you need to edit the mapped attributes before importing.',
        ],
    ],

    'delete' => [
        'confirm' => 'Apakah Anda yakin untuk menghapus aset ini?',
        'error' => 'Terdapat kesalahan pada saat penghapusan aset. Silahkan coba kembali.',
        'assigned_to_error' => '{1}Tag Aset: :asset_tag saat ini sedang diperiksa. Periksa perangkat ini sebelum dihapus.|[2,*]Tag Aset: :asset_tag saat ini sedang diperiksa. Periksa perangkat ini sebelum dihapus.',
        'nothing_updated' => 'Tidak ada aset yang dipilih, jadi tidak ada yang dihapus.',
        'success' => 'Aset sukses terhapus.',
    ],

    'checkout' => [
        'error' => 'Aset gagal di berikan, silahkan coba kembali',
        'success' => 'Sukses memberikan aset.',
        'user_does_not_exist' => 'Pengguna tersebut tidak terdaftar. Silahkan coba kembali.',
        'not_available' => 'Aset tersebut tidak tersedia untuk checkout!',
        'no_assets_selected' => 'Anda harus memilih setidaknya satu aset dari daftar',
    ],

    'multi-checkout' => [
        'error' => 'Aset tidak dapat dipinjamkan, harap coba lagi.|Aset tidak dapat dipinjamkan, harap coba lagi',
        'success' => 'Aset berhasil dipinjamkan.|Aset berhasil dipinjamkan.',
    ],

    'multi-checkin' => [
        'error' => 'Asset was not checked in, please try again|Assets were not checked in, please try again',
        'success' => 'Asset checked in successfully.|Assets checked in successfully.',
        'no_assets_selected' => 'Anda harus memilih setidaknya satu aset dari daftar',
    ],

    'multi-audit' => [
        'success' => ':count asset audited successfully.|:count assets audited successfully.',
        'partial_error' => ':success asset audited, :failed failed. Check the errors below and try again.|:success assets audited, :failed failed. Check the errors below and try again.',
        'no_assets_selected' => 'Anda harus memilih setidaknya satu aset dari daftar',
    ],

    'checkin' => [
        'error' => 'Aset gagal di terima, silahkan coba kembali',
        'success' => 'Sukses menerima aset.',
        'updated_without_checkin' => 'Asset was already checked in, but the form fields you set (status, location, name, notes) were applied anyway.',
        'user_does_not_exist' => 'Pengguna tersebut tidak terdaftar. Silahkan coba kembali.',
        'already_checked_in' => 'Aset tersebut telah di terima.',
        'force_checkin_orphaned_success' => 'Invalid assignment cleared successfully.',
        'force_checkin_not_orphaned' => 'Item is not in an invalid assignment state.',
        'force_checkin_error' => 'Could not clear invalid assignment.',

    ],

    'requests' => [
        'error' => 'Permintaan tidak berhasil, silakan coba lagi.',
        'success' => 'Permintaan berhasil dikirim.',
        'canceled' => 'Permintaan berhasil dibatalkan.',
        'cancel' => 'Batalkan permintaan barang ini',
        'duplicate' => 'You already have an active request for this item.',
        'no_active' => 'You have no active request to cancel for this item.',
        'insufficient_stock' => 'Not enough on hand to fulfill this request. Replenish first.',
        'confirm_cancel_by_admin' => "Cancel :user's request for :item?",
        'no_selection' => 'No rows were selected to fulfill.',
        'row_stale' => 'Request #:id is no longer pending and was skipped.',
        'row_qty_invalid' => 'Request #:id has an invalid quantity and was skipped.',
        'row_user_missing' => 'Request #:id has no valid target user and was skipped.',
        'row_asset_missing' => 'Request #:id has no valid target asset and was skipped.',
        'row_asset_not_requesters' => 'Request #:id skipped: selected asset does not belong to the requester.',
        'row_asset_not_available' => 'Request #:id skipped: selected asset is not an available unit of this model.',
        'row_asset_taken' => 'Request #:id skipped: selected asset was assigned to someone else before submit.',
        'row_company_mismatch' => 'Request #:id skipped: :user cannot receive items from this company.',
        'row_over_allocated' => 'Request #:id skipped: not enough on hand at the moment of submit.',
        'no_target_assets_for_user' => ':user has no assets to install into.',
        'no_available_units' => 'No available units of this model to hand out.',
        'bulk_summary' => 'Fulfilled :fulfilled of :total requests.',
        'bulk_fulfill_notification_intro' => 'The following will apply to each ticked request when fulfilled:',
    ],

];
