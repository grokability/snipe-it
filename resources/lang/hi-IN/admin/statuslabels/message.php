<?php

return [

    'does_not_exist' => 'Status Label does not exist.',
    'deleted_label' => 'Deleted Status Label',
    'assoc_assets'	 => 'This Status Label is currently associated with at least one Asset and cannot be deleted. Please update your assets to no longer reference this status and try again. ',

    'create' => [
        'error'   => 'Status Label was not created, please try again.',
        'success' => 'Status Label created successfully.',
    ],

    'update' => [
        'error'   => 'Status Label was not updated, please try again',
        'success' => 'Status Label updated successfully.',
    ],

    'delete' => [
        'confirm'   => 'Are you sure you wish to delete this Status Label?',
        'error'   => 'There was an issue deleting the Status Label. Please try again.',
        'success' => 'The Status Label was deleted successfully.',
    ],

    'help' => [
        'undeployable'   => 'These assets cannot be assigned to anyone.',
        'deployable'   => 'These assets can be checked out. Once they are assigned, they will assume a meta status of <i class="fas fa-circle text-blue"></i> <strong>Deployed</strong>.',
        'archived'   => 'These assets cannot be checked out, and will only show up in the Archived view. This is useful for retaining information about assets for budgeting/historic purposes but keeping them out of the day-to-day asset list.',
        'pending'   => 'These assets can not yet be assigned to anyone, often used for items that are out for repair, but are expected to return to circulation.',
    ],

];
