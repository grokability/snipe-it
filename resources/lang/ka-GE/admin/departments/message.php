<?php

return array(

    'does_not_exist' => 'Department does not exist.',
    'department_already_exists' => 'A department already exists with that name at this company location. Or choose a more specific name for this department. ',
    'assoc_users'	 => 'This department is currently associated with at least one user and cannot be deleted. Please update your users to no longer reference this department and try again. ',
    'create' => array(
        'error'   => 'Department was not created, please try again.',
        'success' => 'Department created successfully.'
    ),
    'update' => array(
        'error'   => 'Department was not updated, please try again',
        'success' => 'Department updated successfully.'
    ),
    'delete' => array(
        'confirm'   	=> 'Are you sure you wish to delete this department?',
        'error'   => 'There was an issue deleting the department. Please try again.',
        'success' => 'The department was deleted successfully.'
    )

);
