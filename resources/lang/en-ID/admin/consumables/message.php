<?php

return [

    'invalid_category_type' => 'The category must be a consumable category.',
    'does_not_exist' => 'Consumable Tidak ada.',

    'create' => [
        'error' => 'Consumable gagal dibuat, silahkan coba lagi.',
        'success' => 'Consumable berhasil dibuat.',
    ],

    'update' => [
        'error' => 'Consumable gagal diupdate, silahkan coba lagi',
        'success' => 'Consumable berhasil diupdate.',
    ],

    'delete' => [
        'confirm' => 'Apakah Anda yakin ingin menghapus Consumable ini?',
        'error' => 'Ada masalah menghapus Consumable. Silakan coba lagi.',
        'success' => 'Consumable berhasil dihapus.',
        'bulk_success' => 'Consumable deleted successfully.|:count consumables were deleted successfully.',
        'partial_success' => ':count consumable was deleted successfully, but others could not be deleted. See below for details.|:count consumables were deleted successfully, but others could not be deleted. See below for details.',
    ],

    'checkout' => [
        'error' => 'Consumable tidak diperiksa, silakan coba lagi',
        'success' => 'Consumable berhasil diperiksa.',
        'user_does_not_exist' => 'Pengguna tidak valid. Silakan coba lagi.',
        'unavailable' => 'There are not enough consumables for this checkout. Please check the quantity left. ',
    ],

    'consumed' => [
        'success' => 'Distribution marked as consumed.',
        'bulk_success' => ':count distribution was marked as consumed.|:count distributions were marked as consumed.',
        'already' => 'That distribution was already marked as consumed.',
        'error' => 'Could not mark the distribution as consumed, please try again.',
        'no_rows_selected' => 'No valid distributions were selected.',
    ],

    'checkin' => [
        'error' => 'Consumable tidak diperiksa, silakan coba lagi',
        'success' => 'Consumable berhasil di-check in.',
        'user_does_not_exist' => 'Pengguna tidak valid. Silakan coba lagi.',
    ],

    'restore' => [
        'error' => 'Consumable was not restored, please try again',
        'success' => 'Consumable restored successfully.',
    ],

    'bulk' => [
        'no_consumables_selected' => 'No consumables were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Tidak ada bidang yang berubah, jadi tidak ada yang diperbarui.',
        'skipped_unauthorized' => ':count consumable(s) were skipped because you do not have permission to update them.',

        'update' => [
            'success' => ':count consumable was updated.|:count consumables were updated.',
            'none_updated' => 'The selected consumable could not be updated. See errors below for details.|None of the selected consumables could be updated. See errors below for details.',
        ],

        'restore' => [
            'success' => ':count consumable was restored.|:count consumables were restored.',
        ],
    ],
];
