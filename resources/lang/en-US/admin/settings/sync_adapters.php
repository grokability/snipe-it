<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Sync Adapter Strings
    |--------------------------------------------------------------------------
    |
    | Every UI-facing string for the sync-adapter framework, grouped into
    | sections. Placeholder tokens like :vendor, :type, :label, :count,
    | :summary, :pull_command, and :push_command let translators localize
    | each shape once no matter which adapter renders it.
    |
    */

    // Framework labels and messages
    'title' => 'Sync Adapters',
    'help' => 'Sync host inventory from external systems into Snipe-IT.',
    'none_registered' => 'No sync adapters are registered in this installation.',
    'base_url' => 'Base URL',
    'base_url_help' => 'The base URL of your :type instance.',
    'pull_now' => 'Pull Now',
    'push_now' => 'Push Now',
    'push_complete' => 'Push complete. Pushed :count asset(s), :errors error(s).',
    'push_failed' => 'Push failed: :summary. See storage/logs/sync-adapters.log for full details.',
    'push_not_supported' => 'This adapter does not support pushing data to the vendor.',
    'not_configured' => 'This adapter has not been configured yet.',
    'sync_complete' => 'Sync complete. Synced :count host(s), :errors error(s).',
    'sync_failed' => 'Sync failed: :summary. See storage/logs/sync-adapters.log for full details.',
    'sync_failed_network' => 'network error',
    'last_synced_label' => 'Last Synced',
    'never_synced' => 'Never',
    'large_fleet_note' => 'For large fleets, schedule these CLI commands instead of clicking the buttons above: :pull_command to pull inventory, :push_command to push Snipe-IT values back to the vendor.',
    'large_fleet_note_pull_only' => 'For large fleets, schedule the CLI command :pull_command instead of clicking Pull Now.',
    'not_found' => 'Adapter ":slug" does not exist. It may have been deleted or the URL is out of date.',
    'instance_created' => 'Adapter added. Fill in the URL and credentials below, then check "Active" and Save to enable sync.',
    'instance_deleted' => 'Adapter removed. Previously synced assets stay in place with their existing external-id links.',
    'builtin_undeletable' => 'Built-in adapters cannot be deleted. Deactivate it instead.',

    // Add / edit / delete instance
    'add_button' => 'Add adapter',
    'add_modal_title' => 'Add sync adapter',
    'add_type_label' => 'Adapter type',
    'add_label_label' => 'Label',
    'add_label_help' => 'Shown on the tab. Choose something you will recognize (e.g. "Production Fleet", "Staging Fleet").',
    'add_company_label' => 'Company',
    'add_company_help' => 'Leave blank to make this adapter available to all companies.',
    'delete_button' => 'Delete this adapter',
    'delete_confirm' => 'Delete this adapter? Previously-synced assets stay in place but stop updating from this source.',
    'company_filter_label' => 'Filter by company',
    'company_filter_all' => 'All companies',
    'company_filter_shared' => 'Shared (no company)',

    // Readiness + direction states
    'readiness_active' => 'Active, configured and will sync',
    'readiness_partial' => 'Turned on but the configuration is incomplete',
    'readiness_inactive' => 'Turned off',
    'direction_pull' => 'Pull from vendor',
    'direction_push' => 'Push to vendor',
    'direction_both' => 'Both (last-write-wins)',
    'direction_skip' => 'Skip',
    'active_label' => 'Active',
    'active_help' => 'When off, scheduled runs and manual Pull Now / Push Now clicks all skip this adapter. Stored credentials stay intact so you can re-enable without reconfiguring.',

    // Field-mapping fieldset
    'mapping_section_title' => ':type field mapping',
    'mapping_section_intro' => 'If a field is mapped to a custom field the asset\'s model does not include, the value is imported but stays invisible on the asset detail view until an admin adds that field to the model\'s fieldset.',
    'field_mapping_title' => 'Field mapping',
    'field_mapping_intro' => 'Every adapter maps host records into your Snipe-IT assets the same way.',
    'field_mapping_source' => 'From the sync source',
    'field_mapping_target' => 'Snipe-IT asset field',
    'field_hostname' => 'Hostname',
    'field_serial' => 'Hardware serial',
    'field_asset_tag' => 'Asset tag',
    'field_model' => 'Hardware model',
    'field_mac' => 'Primary MAC address',
    'field_ip' => 'Primary IP address',
    'field_os' => 'Operating system',
    'field_os_version' => 'OS version',
    'field_last_seen' => 'Last seen (network heartbeat)',
    'field_asset_name' => 'Asset name',
    'field_asset_serial' => 'Asset serial number',
    'field_asset_model' => 'Asset model (auto-created if missing)',
    'field_also_pulled_title' => 'Also pulled',
    'field_also_pulled_intro' => 'These values are pulled and saved alongside the asset. They are not surfaced in any dedicated UI yet.',

    // Mapping targets (dropdown labels)
    'target_skip' => 'Skip (do not sync)',
    'target_default_suffix' => '(default)',
    'target_custom_prefix' => 'Custom field',
    'target_native_name' => 'Asset name',
    'target_native_serial' => 'Asset serial number',
    'target_native_model' => 'Asset model (auto-created)',
    'target_native_asset_tag' => 'Asset tag',
    'target_native_notes' => 'Asset notes',
    'target_external_mac' => 'MAC address',
    'target_external_ip' => 'IP address',
    'target_external_os' => 'OS',
    'target_external_os_version' => 'OS version',
    'target_external_last_seen' => 'Last seen',

    // Operational settings
    'log_heartbeats_label' => 'Log every sync, including heartbeats',
    'log_heartbeats_help' => 'By default, syncs that only update the last-seen timestamp are not written to the asset history to avoid heartbeat noise. Turn this on to record every sync as a history entry.',
    'asset_tag_pattern' => 'Asset tag pattern',
    'asset_tag_pattern_help' => 'Applied only to newly-created assets from this adapter. Supported placeholders: <code>{serial}</code>, <code>{external_id}</code>, <code>{hostname}</code>, <code>{model}</code>, <code>{source}</code>. Leave blank to fall back to Snipe-IT\'s auto-increment setting. If auto-increment is also off, the asset tag defaults to <code>{source}-{external_id}</code>. Assets already synced keep their existing asset tag.',
    'default_category' => 'Model Category',
    'default_category_help' => 'When a device from this adapter reports a hardware model that doesn\'t exist in Snipe-IT yet, a new asset model is created and placed in this category. Leave blank to use the "Discovered Hardware" category.',
    'default_status' => 'Default Status Label',
    'default_status_help' => 'When a device from this adapter is created in Snipe-IT for the first time, its status label is set to this value. Leave blank to use the first deployable status label (or the first status label if none are marked deployable).',
    'user_match_strategy' => 'Assign to Snipe-IT user',
    'user_match_strategy_help' => 'When a match is found, the asset checks out to that user. Missing or unmatched users are skipped and written to <code>storage/logs/sync-adapters.log</code> as warnings. Existing assignments are never cleared by a payload that omits the user field.',
    'user_match_none' => 'Do not assign users',
    'user_match_username_then_email' => 'Match by username, fall back to email',
    'user_match_username' => 'Match by username only',
    'user_match_email' => 'Match by email address only',
    'suppress_notifications_label' => 'Suppress notifications (email, webhooks) on sync-driven assignments',
    'checkin_on_null_user_label' => 'Check assets in when the vendor reports no assigned user',
    'checkin_on_null_user_help' => 'When the vendor stops reporting an assigned user for a device, check the asset in from whoever had it. Off by default because a single missed sync cycle (device offline, empty field on a fresh enrollment) would unassign the asset. Turn this on only if you trust your vendor\'s user reporting to be consistent every sync.',

    // Push dry-run + composite notes push
    'push_dry_run_label' => 'Dry-run push (log payloads, do not send)',
    'push_dry_run_help' => 'When on, Push Now builds every payload and writes it to the sync-adapters log without calling the vendor API. Useful for verifying your direction + mapping configuration end-to-end before flipping the switch on real data.',
    'push_notes_section_title' => 'Composite Field',
    'push_notes_section_intro' => 'Optional composite field where you can assemble multiple attributes from an asset and push it into a compatible field on the vendor side. This is useful for vendors that do not support multiple fields or custom attributes, but do support a single notes field.',
    'push_notes_target_label' => 'Vendor field',
    'push_notes_target_help' => 'Vendor field the composed notes get written to. Leave blank to use the adapter\'s default (shown as placeholder). Set to a Custom Attribute / Custom Field name if you want the composed notes to be pushed to a specific vendor-side field instead of the default notes column.',
    'push_notes_target_placeholder_none' => 'No default (specify a target)',
    'push_notes_template_label' => 'Template',
    'push_notes_template_help' => 'Blade-style template rendered per asset and pushed to the vendor field above. Leave blank to skip notes push. Placeholders: <code>{asset_tag}</code>, <code>{name}</code>, <code>{serial}</code>, <code>{model}</code>, <code>{manufacturer}</code>, <code>{category}</code>, <code>{status}</code>, <code>{status_type}</code>, <code>{assigned_to}</code>, <code>{assigned_to_email}</code>, <code>{assigned_to_username}</code>, <code>{location}</code>, <code>{company}</code>, <code>{supplier}</code>, <code>{last_checkout}</code>, <code>{last_checkin}</code>, <code>{expected_checkin}</code>, <code>{notes}</code>, <code>{order_number}</code>, <code>{purchase_date}</code>, <code>{purchase_cost}</code>, <code>{warranty_months}</code>, <code>{warranty_expires}</code>. Custom fields: <code>{custom.Field Name}</code>. Unknown or empty placeholders render as blank.',

    // Group scoping
    'group_mapping_title' => ':label to Snipe-IT company mapping',
    'group_mapping_intro' => 'Map each :label from the vendor to a Snipe-IT company. Synced devices land in the mapped company. Unmapped groups fall back to this adapter\'s own company setting. Click Refresh to pull the current list from the vendor.',
    'group_mapping_empty' => 'No :label list loaded yet. Click Refresh to fetch the current list from the vendor.',
    'refresh_groups' => 'Refresh :label list',
    'refresh_groups_ok' => 'Refreshed :count :label(s) from the vendor.',
    'refresh_groups_failed' => 'Refresh failed: :summary. See storage/logs/sync-adapters.log for full details.',

    // Vendor custom fields refresh
    'refresh_custom_fields' => 'Refresh vendor custom fields',
    'refresh_custom_fields_ok' => 'Refreshed :count vendor custom field(s). Each is now available as a mapping target below.',
    'refresh_custom_fields_failed' => 'Refresh failed: :summary. See storage/logs/sync-adapters.log for full details.',

    // Per-adapter credential help
    'fleet_token_help' => 'Generate under My Account -> Get API token in your Fleet instance. Needs an admin or observer role with read access to hosts (and to teams if you use group-to-company mapping).',
    'kandji_token_help' => 'Generate under Settings -> Access -> API Token in your Kandji tenant. Needs the Device list read permission (and Blueprint list if you use group-to-company mapping).',
    'jamf_token_help' => 'Generate a Personal Access Token under Settings -> System -> API Roles and Clients in Jamf Pro. The role needs Read on Computers (and Sites if you use group-to-company mapping).',
    'jamf_school_network_id_help' => 'Find your Network ID under Organization -> Settings -> API in the Jamf School admin console.',
    'jamf_school_api_key_help' => 'Generate under Organization -> Settings -> API in Jamf School. Needs read access to Devices (and Locations if you use group-to-company mapping).',
    'jumpcloud_token_help' => 'Copy from your JumpCloud admin user\'s profile page (top-right menu -> My API Key). API keys inherit the admin user\'s permissions, so use an account with read access to Systems.',
    'mosyle_token_help' => 'Generate under Account -> API in your Mosyle admin console (Manager or Business). Needs read access to Devices.',
    'osctrl_token_help' => 'Generate under Users -> API tokens in your osctrl admin console. The user issuing the token needs read access to the target environment\'s nodes.',
    'osctrl_environment_help' => 'The osctrl environment name to sync from (e.g. prod, dev).',
    'addigy_key_id_help' => 'Generate under Integrations -> Addigy API in your Addigy admin console. Both Client ID and Client Secret come from the same "Create API Integration" flow. Client ID is a public identifier and not encrypted at rest.',
    'addigy_key_secret_help' => 'Paired with Client ID above. Displayed once in the Addigy admin console at creation time. Needs read access to Devices.',
    'unifi_api_key_help' => 'Generate under Settings -> Admins & Users -> API Keys in your UniFi Network application (UniFi OS 4.0.6+ / Network 9.0.108+ required). Older controllers use session cookies and are not supported.',
    'unifi_site_id_help' => 'The UniFi site to sync from. Use "default" for the primary site, or the site\'s ID from Settings -> System -> Advanced.',
    'intune_tenant_id_help' => 'Copy your Azure AD tenant\'s Directory (tenant) ID from Microsoft Entra admin center -> Overview.',
    'intune_client_id_help' => 'Register an application in Microsoft Entra ID -> App registrations, then copy the Application (client) ID. Grant it the DeviceManagementManagedDevices.Read.All Microsoft Graph application permission (admin consent required).',
    'intune_client_secret_help' => 'Generate under your app registration -> Certificates & secrets -> New client secret. Copy the value immediately (Azure only shows it once).',
    'meraki_sm_api_key_help' => 'Generate under your Meraki Dashboard profile -> My Profile -> API access. Needs read access to the target organization (and Systems Manager network access for the devices endpoint).',
    'meraki_sm_organization_id_help' => 'Copy from Organization -> Settings -> Dashboard API access in the Meraki Dashboard, or from any org URL segment after /o/.',
    'workspace_one_tenant_code_help' => 'Find under Groups & Settings -> All Settings -> System -> Advanced -> API -> REST API in your Workspace ONE console.',
    'workspace_one_client_id_help' => 'Create an OAuth client under Groups & Settings -> Configurations -> OAuth Client Management. Grant it a role with read access to Devices.',
    'workspace_one_client_secret_help' => 'Paired with Client ID above. Workspace ONE displays the secret once at OAuth client creation time.',
    'zentral_token_help' => 'Generate a service-account API token under Setup -> User -> Users in your Zentral console. Needs the "Django REST Framework Token" flag and read access to the inventory app.',
    'ninjaone_client_id_help' => 'Register an app under Administration -> Apps -> API in your NinjaOne dashboard. Set the authorization type to "Client Credentials" and grant it the Monitoring scope so it can read devices. Copy the Client ID from the app detail page.',
    'ninjaone_client_secret_help' => 'Paired with Client ID above. NinjaOne shows the secret once when the OAuth app is created.',
    'ninjaone_asset_tag_field_help' => 'Optional. NinjaOne has no built-in asset_tag column, so push writes to a per-device Custom Field instead. Create the field under Administration -> Devices -> Custom Fields in your Ninja dashboard, then paste its exact name here. Leave blank if you are not pushing asset_tag to Ninja.',
    'kaseya_vsa10_token_id_help' => 'Generate an API token pair under Settings -> Extensions -> API Tokens in your VSA 10 admin console. Grant the token read access to the Devices resource. Copy the Token ID (the first half of the pair) here.',
    'kaseya_vsa10_token_secret_help' => 'Paired with the Token ID above. VSA 10 shows the secret once at token creation time.',
    'abm_mode_help' => 'The Apple portal your organization uses. Determines the API host and OAuth scope the adapter connects to.',
    'abm_client_id_help' => 'Create an API key under Settings -> API in your Apple Business Manager or Apple School Manager console. Copy the Client ID from the key detail page.',
    'abm_key_id_help' => 'Paired with the Client ID. Shown on the same key detail page in the ABM/ASM console.',
    'abm_private_key_help' => 'Paste the contents of the .pem private-key file you downloaded when you created the API key. Apple only lets you download the key once.',
    'abm_product_family_filter_help' => 'Only sync devices in the selected product families.',
    'abm_pull_model_images_help' => 'Fetch product images from appledb.dev when auto-populating asset models from ABM devices. Existing model images are never overwritten. The category image serves only as a display fallback for models that have none.',
    'abm_category_family_label' => 'Category for :family',
    'abm_category_family_help' => 'Route the :family product family to a specific category, overriding the default category above. Leave blank to inherit the default.',

    // Extras / mapping section wrappers
    'extra_fields_section_title' => ':type-specific fields',
    'extra_fields_section_intro' => 'Additional vendor fields that don\'t have a corresponding Snipe-IT field. ',

    /*
    |--------------------------------------------------------------------------
    | Extras Field Labels (used via label_key in adapters' extraFields())
    |--------------------------------------------------------------------------
    |
    | Each string uses :vendor as a placeholder for the adapter's product
    | name (Fleet, Jamf Pro, Apple Business Manager, ...) so translators
    | localize each shape once and every adapter that emits that shape
    | reads through the same key. Where the concept is single-vendor
    | (Blueprint ID for Kandji, UDID for Jamf), the placeholder still
    | applies so translation stays consistent if a second vendor adopts
    | the same terminology later.
    |
    */
    'extra_agent_version' => ':vendor Agent Version',
    'extra_architecture' => ':vendor Architecture',
    'extra_model_marketing_name' => ':vendor Model Marketing Name',
    'extra_model_identifier' => ':vendor Model Identifier',
    'extra_supervised' => ':vendor Supervised',
    'extra_mdm_enabled' => ':vendor MDM Enabled',
    'extra_mdm_server' => ':vendor MDM Server',
    'extra_labels' => ':vendor Labels',
    'extra_tags' => ':vendor Tags',
    'extra_team' => ':vendor Team',
    'extra_uuid' => ':vendor UUID',
    'extra_udid' => ':vendor UDID',
    'extra_status' => ':vendor Status',
    'extra_active' => ':vendor Active',
    'extra_group' => ':vendor Group',
    'extra_site' => ':vendor Site',
    'extra_location_id' => ':vendor Location ID',
    'extra_policy_id' => ':vendor Policy ID',
    'extra_domain' => ':vendor Domain',
    'extra_cpu' => ':vendor CPU',
    'extra_cpu_type' => ':vendor CPU Type',
    'extra_os_family' => ':vendor OS Family',
    'extra_environment' => ':vendor Environment',
    'extra_last_enrolled' => ':vendor Last Enrolled',
    'extra_computer_type' => ':vendor Computer Type',
    'extra_user_id' => ':vendor User ID',
    'extra_model_code' => ':vendor Model Code',
    'extra_device_state' => ':vendor Device State',
    'extra_uplink_mac' => ':vendor Uplink MAC',
    'extra_adopted' => ':vendor Adopted',
    'extra_marked_missing' => ':vendor Marked Missing',
    'extra_blueprint_id' => ':vendor Blueprint ID',
    'extra_product_family' => ':vendor Product Family',
    'extra_product_type' => ':vendor Product Type',
    'extra_part_number' => ':vendor Part Number',
    'extra_color' => ':vendor Color',
    'extra_order_number' => ':vendor Order Number',
    'extra_order_date' => ':vendor Order Date',
    'extra_purchase_source_type' => ':vendor Purchase Source Type',
    'extra_purchase_source_id' => ':vendor Purchase Source ID',
    'extra_applecare_agreement_number' => 'AppleCare Agreement Number',
    'extra_applecare_status' => 'AppleCare Status',
    'extra_applecare_payment_type' => 'AppleCare Payment Type',
    'extra_applecare_start_date' => 'AppleCare Start Date',
    'extra_applecare_end_date' => 'AppleCare End Date',
    'extra_applecare_description' => 'AppleCare Description',
    'extra_applecare_is_canceled' => 'AppleCare Is Canceled',
    'extra_applecare_is_renewable' => 'AppleCare Is Renewable',
    'extra_osquery_version' => 'osquery Version',
];
