<?php

return [

    // -----------------------------------------------------------------
    // Generic errors
    // -----------------------------------------------------------------
    'not_authenticated' => 'Not authenticated',
    'unauthorized' => 'Unauthorized',
    'scope_denied' => 'This token does not have the :scope scope required for this action',
    'scope_handshake_required' => 'This token does not carry any MCP scopes. Re-authorize the connector and grant at least one mcp:* scope before calling MCP tools.',
    'scope_session_description' => 'Open an MCP session and discover available tools. Does NOT grant permission to call any tool. Capability scopes (read, write, etc.) are required for tool calls.',
    'scope_read_description' => 'View assets, users, licenses, and other inventory data',
    'scope_write_description' => 'Create or edit inventory items and their attributes',
    'scope_delete_description' => 'Soft-delete items and restore deleted ones',
    'scope_checkout_description' => 'Check assets, accessories, components, consumables, and licenses out to users, locations, or assets',
    'scope_checkin_description' => 'Check assets, accessories, components, and licenses back in',
    'scope_superadmin_description' => 'Manage permission groups. Required for group create / update / delete and viewing the group list',
    'id_or_name_required' => 'Either id or name is required',
    'id_username_or_email_required' => 'Provide an id, username, or email',
    'provide_user_or_asset' => 'Provide either assigned_to (user ID) or asset_id',

    // -----------------------------------------------------------------
    // "Not found" errors
    // -----------------------------------------------------------------
    'object_not_found' => 'The specified :type was not found',
    'asset_not_found' => 'Asset not found',
    'user_not_found' => 'BenutzerIn nicht gefunden',
    'accessory_not_found' => 'Accessory not found',
    'accessory_checkout_not_found' => 'Accessory checkout record not found',
    'component_not_found' => 'Component not found',
    'component_checkout_not_found' => 'Component checkout record not found',
    'consumable_not_found' => 'Consumable not found',
    'license_not_found' => 'Lizenz nicht gefunden',
    'license_seat_not_found' => 'License seat not found',
    'department_not_found' => 'Department not found',
    'company_not_found' => 'Company not found',
    'category_not_found' => 'Category not found',
    'manufacturer_not_found' => 'Manufacturer not found',
    'supplier_not_found' => 'Lieferant nicht gefunden',
    'status_label_not_found' => 'Status label not found',
    'location_not_found' => 'Location not found',
    'asset_model_not_found' => 'Asset model not found',
    'depreciation_not_found' => 'Depreciation not found',
    'group_not_found' => 'Group not found',
    'checkout_target_not_found' => 'The specified :type was not found',

    // -----------------------------------------------------------------
    // Business rule errors
    // -----------------------------------------------------------------
    'asset_not_available' => 'Asset :asset_tag is not available for checkout',
    'asset_not_checked_out' => 'Asset :asset_tag is not currently checked out',
    'asset_not_deleted' => 'Asset is not deleted',
    'user_not_deleted' => 'User is not deleted',
    'user_cannot_delete_self' => 'You cannot delete your own account',
    'user_has_items' => 'User has assigned items and cannot be deleted. Check in all items first.',
    'cannot_edit_auth_fields' => 'You do not have permission to edit auth fields (username, email, password, activated) for this user',
    'username_taken' => 'Username :username is already taken. Choose a different username.',
    'password_reset_sent' => 'Password reset link sent to :email',
    'password_reset_user_inactive' => 'User :username is inactive and cannot receive a password reset email',
    'password_reset_no_email' => 'User :username has no email address on file',
    'password_reset_ldap_user' => 'User :username is managed by LDAP and cannot use password reset',
    'password_reset_send_failed' => 'Failed to send password reset email: :error',
    'invalid_permissions_format' => 'Permissions must be a valid JSON object',
    'invalid_permission_key' => 'Invalid permission key: :key',
    'invalid_permission_value' => 'Permission value for :key must be 1 (grant) or -1 (deny)',
    'superadmin_required_for_groups' => 'Only superadmins can assign permission groups',
    'no_available_seats' => 'No available seats for this license',
    'no_free_seat' => 'No free seat found for this license',
    'seat_not_checked_out' => 'This seat is not currently checked out',
    'no_units_available' => 'No units of this accessory are available for checkout',
    'no_units_remaining' => 'No units remaining',
    'accessory_has_checkouts' => 'Accessory has units checked out and cannot be deleted. Check them in first.',
    'component_has_checkouts' => 'Component has units checked out and cannot be deleted. Check them in first.',
    'consumable_has_checkouts' => 'Consumable has items checked out and cannot be deleted',
    'license_has_seats_assigned' => 'License has seats currently assigned and cannot be deleted. Check in all seats first.',
    'department_has_users' => 'Department has users assigned and cannot be deleted. Reassign all users first.',
    'location_has_child_locations' => 'Location has child locations and cannot be deleted',
    'location_has_users' => 'Location has users assigned and cannot be deleted',
    'status_label_has_assets' => 'Status label has assets assigned and cannot be deleted',
    'model_has_assets' => 'Model has assets and cannot be deleted',
    'bulk_confirm_count_required' => 'confirm_count is required when deleting by filter. Preview the batch with dry_run=true first, then re-call with the exact total_matched value as confirm_count.',
    'bulk_confirm_count_mismatch' => 'confirm_count :expected does not match the actual row count :actual. The filter result may have changed since the preview. Re-run with dry_run=true and try again.',
    'bulk_batch_has_undeletable' => 'Batch includes :count row(s) that cannot be deleted (checked out, has children, etc). Pass skip_undeletable=true to drop them and delete the rest, or remove the blockers and retry.',
    'bulk_dry_run_summary' => 'Preview: :total row(s) matched, :deletable will delete on commit. No changes made.',
    'bulk_delete_completed' => 'Bulk delete complete. :count row(s) removed.',
    'bulk_delete_failed' => 'Bulk delete failed and was rolled back. Reason: :failures',

    // -----------------------------------------------------------------
    // Operation failures
    // -----------------------------------------------------------------
    'create_failed' => 'Create failed: :error',
    'update_failed' => 'Update failed: :error',
    'delete_failed' => 'Delete failed',
    'delete_failed_error' => 'Delete failed: :error',
    'checkin_failed' => 'Checkin failed',
    'checkin_failed_error' => 'Checkin failed: :error',
    'checkout_failed' => 'Checkout failed',
    'audit_failed' => 'Audit failed: :error',
    'note_save_failed' => 'Failed to save note',

    // -----------------------------------------------------------------
    // Asset messages
    // -----------------------------------------------------------------
    'asset_found' => 'Asset :asset_tag found',
    'asset_created' => 'Asset :asset_tag created successfully',
    'asset_updated' => 'Asset :asset_tag updated successfully',
    'asset_deleted' => 'Asset :asset_tag deleted successfully',
    'asset_restored' => 'Asset :asset_tag restored successfully',
    'asset_checked_out' => 'Asset :asset_tag checked out successfully',
    'asset_checked_in' => 'Asset :asset_tag checked in successfully',
    'asset_audited' => 'Audit recorded for asset :asset_tag',
    'note_added_to_asset' => 'Note added to asset :asset_tag',
    'note_added_successfully' => 'Note added successfully',

    // -----------------------------------------------------------------
    // User messages
    // -----------------------------------------------------------------
    'current_user' => 'Current user: :username',
    'user_found' => 'User :username found',
    'user_created' => 'User :username created successfully',
    'user_updated' => 'User :username updated successfully',
    'user_deleted' => 'User :username deleted successfully',
    'user_restored' => 'User :username restored successfully',
    'two_factor_reset' => 'Two-factor authentication reset for :username',
    'user_assets_found' => 'Found :count assets for user :username',
    'user_permissions_found' => 'User :username has :count effective permissions',
    'profile_updated' => 'Profile updated successfully',

    // -----------------------------------------------------------------
    // Accessory messages
    // -----------------------------------------------------------------
    'accessory_created' => 'Accessory :name created successfully',
    'accessory_updated' => 'Accessory :name updated successfully',
    'accessory_deleted' => 'Accessory :name deleted successfully',
    'accessory_checked_out' => 'Accessory :name checked out successfully',
    'accessory_checked_in' => 'Accessory :name checked in successfully',

    // -----------------------------------------------------------------
    // Component messages
    // -----------------------------------------------------------------
    'component_created' => 'Component :name created successfully',
    'component_updated' => 'Component :name updated successfully',
    'component_deleted' => 'Component :name deleted successfully',
    'component_checked_out' => 'Component :name checked out to asset :asset_tag',
    'component_checked_in' => 'Component :name checked in successfully',

    // -----------------------------------------------------------------
    // Consumable messages
    // -----------------------------------------------------------------
    'consumable_found' => 'Consumable :name found',
    'consumable_created' => 'Consumable :name created successfully',
    'consumable_updated' => 'Consumable :name updated successfully',
    'consumable_deleted' => 'Consumable :name deleted successfully',
    'consumable_checked_out' => 'Consumable :name checked out to :username',

    // -----------------------------------------------------------------
    // License messages
    // -----------------------------------------------------------------
    'license_found' => 'License :name found',
    'license_created' => 'License :name created successfully',
    'license_updated' => 'License :name updated successfully',
    'license_deleted' => 'License :name deleted successfully',
    'license_seat_checked_out_user' => 'License seat checked out to user :username',
    'license_seat_checked_out_asset' => 'License seat checked out to asset :asset_tag',
    'license_seat_checked_in' => 'License seat :id checked in successfully',

    // -----------------------------------------------------------------
    // Department messages
    // -----------------------------------------------------------------
    'department_created' => 'Department :name created successfully',
    'department_updated' => 'Department :name updated successfully',
    'department_deleted' => 'Department :name deleted successfully',

    // -----------------------------------------------------------------
    // Company messages
    // -----------------------------------------------------------------
    'company_found' => 'Company :name found',
    'company_created' => 'Company :name created successfully',
    'company_updated' => 'Company :name updated successfully',
    'company_deleted' => 'Company :name deleted successfully',

    // -----------------------------------------------------------------
    // Category messages
    // -----------------------------------------------------------------
    'category_found' => 'Category :name found',
    'category_created' => 'Category :name created successfully',
    'category_updated' => 'Category :name updated successfully',
    'category_deleted' => 'Category :name deleted successfully',
    'category_delete_failed' => 'Category cannot be deleted: :error',

    // -----------------------------------------------------------------
    // Manufacturer messages
    // -----------------------------------------------------------------
    'manufacturer_found' => 'Manufacturer :name found',
    'manufacturer_created' => 'Manufacturer :name created successfully',
    'manufacturer_updated' => 'Manufacturer :name updated successfully',
    'manufacturer_deleted' => 'Manufacturer :name deleted successfully',

    // -----------------------------------------------------------------
    // Supplier messages
    // -----------------------------------------------------------------
    'supplier_found' => 'Supplier :name found',
    'supplier_created' => 'Supplier :name created successfully',
    'supplier_updated' => 'Supplier :name updated successfully',
    'supplier_deleted' => 'Supplier :name deleted successfully',

    // -----------------------------------------------------------------
    // Status label messages
    // -----------------------------------------------------------------
    'status_label_found' => 'Status label :name found',
    'status_label_created' => 'Status label :name created successfully',
    'status_label_updated' => 'Status label :name updated successfully',
    'status_label_deleted' => 'Status label :name deleted successfully',

    // -----------------------------------------------------------------
    // Location messages
    // -----------------------------------------------------------------
    'location_found' => 'Location :name found',
    'location_created' => 'Location :name created successfully',
    'location_updated' => 'Location :name updated successfully',
    'location_deleted' => 'Location :name deleted successfully',

    // -----------------------------------------------------------------
    // Asset model messages
    // -----------------------------------------------------------------
    'asset_model_found' => 'Asset model :name found',
    'asset_model_created' => 'Asset model :name created successfully',
    'asset_model_updated' => 'Asset model :name updated successfully',
    'asset_model_deleted' => 'Asset model :name deleted successfully',

    // -----------------------------------------------------------------
    // Depreciation messages
    // -----------------------------------------------------------------
    'depreciation_found' => 'Depreciation :name found',
    'depreciation_created' => 'Depreciation :name created successfully',
    'depreciation_updated' => 'Depreciation :name updated successfully',
    'depreciation_deleted' => 'Depreciation :name deleted successfully',

    // -----------------------------------------------------------------
    // Group messages
    // -----------------------------------------------------------------
    'group_found' => 'Group :name found',
    'group_created' => 'Group :name created successfully',
    'group_updated' => 'Group :name updated successfully',
    'group_deleted' => 'Group :name deleted successfully',

    // -----------------------------------------------------------------
    // Maintenance messages
    // -----------------------------------------------------------------
    'maintenance_created' => 'Maintenance :name created successfully',

    // -----------------------------------------------------------------
    // List messages
    // -----------------------------------------------------------------
    'list_assets' => 'Found :total assets, returning :count',
    'list_users' => 'Found :total users, returning :count',
    'list_consumables' => 'Found :total consumables, returning :count',
    'list_licenses' => 'Found :total licenses, returning :count',
    'list_companies' => 'Found :total companies, returning :count',
    'list_categories' => 'Found :total categories, returning :count',
    'list_manufacturers' => 'Found :total manufacturers, returning :count',
    'list_suppliers' => 'Found :total suppliers, returning :count',
    'list_status_labels' => 'Found :total status labels, returning :count',
    'list_locations' => 'Found :total locations, returning :count',
    'list_asset_models' => 'Found :total asset models, returning :count',
    'list_depreciations' => 'Found :total depreciations, returning :count',
    'list_groups' => 'Found :total groups, returning :count',
    'list_maintenances' => 'Found :total maintenances, returning :count',
    'list_activity' => 'Found :total activity log entries, returning :count',
    'list_accessories' => 'Found :total accessories, returning :count',
    'list_maintenance_types' => 'Found :total maintenance types, returning :count',
    'maintenance_type_created' => 'Maintenance type :name created.',
    'find_similar_models_identifier_required' => 'Provide either model_id or model_name to identify the source AssetModel.',
    'find_similar_models_summary' => 'Scored :total candidates similar to :source, returning top :count.',

    // -----------------------------------------------------------------
    // Upload tools
    // -----------------------------------------------------------------
    'id_or_asset_tag_required' => 'Either id or asset_tag is required to identify the asset.',
    'upload_image_decode_failed' => 'The provided image_base64 could not be decoded. Verify the string is valid base64.',
    'upload_image_tempfile_failed' => 'Failed to stage the uploaded image to a temporary file on the server.',
    'upload_image_save_failed' => 'Failed to attach the image. Reason: :error',
    'upload_image_success' => 'Image attached to asset :asset_tag.',
    'list_asset_notes' => 'Found :total notes for asset :asset_tag, returning :count',
    'list_uploads' => 'Found :total uploaded files for :type, returning :count',
    'list_history' => 'Found :total history entries for :type, returning :count',

    // -----------------------------------------------------------------
    // Offboard user tool
    // -----------------------------------------------------------------
    'offboard_identifier_required' => 'Provide either username or first_name plus last_name to identify the user to offboard.',
    'offboard_ambiguous_user' => 'More than one user matched the provided name. Ask the admin which user to offboard and call this tool again with their username.',
    'offboard_dry_run_message' => 'Preview only. No changes have been made. Call this tool again with dry_run=false to execute.',
    'offboard_dry_run_summary' => 'Offboard preview for :username: :assets assets, :accessories accessories, :seats license seats would be released.',
    'offboard_completed_message' => 'Offboarding complete for :username. User account has been soft-deleted.',
    'offboard_completed_summary' => 'Offboarded :username: :assets assets, :accessories accessories, :seats license seats released, user soft-deleted.',
    'offboard_failed' => 'Offboarding failed, rolled back. Reason: :failures',
    'offboard_cross_company_blocked' => 'Cannot offboard :username because they belong to one or more companies you do not have access to. A superuser must offboard this user to avoid orphaning cross-company assignments.',
    'fk_not_found' => 'No record found matching :field = ":value". Pass the correct name, or use :field\'s _id variant with a numeric ID.',
    'fk_ambiguous' => 'More than one record matches :field = ":value". Pass the numeric :id_field instead to disambiguate.',

    // -----------------------------------------------------------------
    // Transfer user assets tool
    // -----------------------------------------------------------------
    'transfer_identifier_required' => 'Provide either username or first_name plus last_name for the :side user.',
    'transfer_user_not_found' => 'No user matched the identifiers given for the :side user.',
    'transfer_ambiguous_user' => 'More than one user matched the :side identifiers. Ask the admin which user to use and call this tool again with their username.',
    'transfer_same_user' => 'Source and target users must be different.',
    'transfer_dry_run_message' => 'Preview only. No changes have been made. Call this tool again with dry_run=false to execute.',
    'transfer_dry_run_summary' => 'Transfer preview from :from to :to: :assets assets, :accessories accessories, :seats license seats would move.',
    'transfer_completed_message' => 'Transfer complete from :from to :to.',
    'transfer_completed_summary' => 'Transferred from :from to :to: :assets assets, :accessories accessories, :seats license seats moved.',
    'transfer_failed' => 'Transfer failed, rolled back. Reason: :failures',

];
