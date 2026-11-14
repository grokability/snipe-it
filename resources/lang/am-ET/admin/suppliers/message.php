<?php

return [

    'deleted' => 'Deleted supplier',
    'does_not_exist' => 'Supplier does not exist.',
    'assoc_items' => 'This supplier is currently associated with at least one asset, accessory, consumable, component, license, or maintenance record and cannot be deleted. Please update those items to no longer reference this supplier and try again. ',

    'create' => [
        'error' => 'Supplier was not created, please try again.',
        'success' => 'Supplier created successfully.',
    ],

    'update' => [
        'error' => 'Supplier was not updated, please try again',
        'success' => 'Supplier updated successfully.',
    ],

    'delete' => [
        'confirm' => 'Are you sure you wish to delete this supplier?',
        'error' => 'There was an issue deleting the supplier. Please try again.',
        'success' => 'Supplier was deleted successfully.',
        'not_found' => 'Supplier not found.',
        'bulk_success' => 'Supplier deleted successfully.|:count suppliers were deleted successfully.',
        'partial_success' => '.',
    ],

];
