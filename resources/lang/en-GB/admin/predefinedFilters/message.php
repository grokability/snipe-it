<?php

return array(

    'does_not_exist' => 'Filter does not exist.',
    'filter_duplicate_name' => 'There is already a predefined filter with the same name.',

    'show' => array(
        'not_allowed' => "You don't have the permissions to see this filter",
    ),

    'create' => array(
        'not_allowed' => "You don't have the permissions to create this public filter",
        'success' => 'Filter created successfully.'
    ),

    'update' => array(
        'not_allowed_to_change_isPublic'=> "You don't have the permissions to make this filter public",
        'at_least_one_is_group_required_for_public_filter' => 'You must select at least one group or set the filter to private.',
        'not_allowed_to_edit'=> "You don't have the permissions to edit this Filter",
        'success' => 'Filter updated successfully.',
        'validation_error'=> 'Something went wrong please set at least a Name, some data to filter with and if public a group',
        'filterData_required' => 'No Filter Data given, is required'
    ),

    'delete' => array(
        'confirm'   => 'Are you sure you wish to delete this filter?',
        'error' => 'something went wrong. please try again',
        'not_allowed_to_delete'=> "You don't have the permissions to delete this Filter",
        'success' => 'The filter was deleted successfully.'
    ),

);
