<?php

return [
    'nav_label' => 'MCP',
    'approved_oauth_clients' => 'Approved OAuth Clients',
    'pending_oauth_clients' => 'Pending OAuth Clients',
    'show_revoked_toggle_show' => 'Show Revoked',
    'show_revoked_toggle_hide' => 'Hide Revoked',

    // Settings page.
    'settings_title' => 'Model Context Protocol (MCP)',
    'settings_intro' => 'OAuth clients that self-register via Dynamic Client Registration (DCR) land in the pending queue below if their redirect URL is not a loopback address (<code>localhost</code>, <code>127.0.0.1</code>) or a trusted URI scheme. Pending clients cannot complete an OAuth flow until you approve them. Admin-created clients and auto-approved registrations go straight to the Approved tab.',

    'trusted_schemes_label' => 'Trusted Custom URI Schemes',
    'trusted_schemes_help' => 'One scheme per line (for example: <code>com.example.myapp</code>). Mobile apps that register with a scheme on this list skip the pending queue. The first-party Snipe-IT mobile app is always trusted. Lines starting with # are treated as comments.',
    'trusted_domains_label' => 'Trusted Redirect Domain Prefixes',
    'trusted_domains_help' => 'One http(s) prefix per line (for example: <code>https://client.example.com</code>). Any redirect URI starting with one of these prefixes skips the pending queue. Loopback (localhost, 127.0.0.1, [::1]) is always trusted. Lines starting with # are treated as comments.',
    'trusted_lists_saved' => 'Trusted lists saved.',

    // Pending queue.
    'pending_header' => 'Pending OAuth Clients',
    'pending_empty' => 'No OAuth clients are awaiting approval.',
    'pending_col_name' => 'Client Name',
    'pending_col_redirect' => 'Redirect URI',
    'pending_col_registered_ip' => 'Registered IP',
    'pending_col_registered_at' => 'Registered',
    'pending_col_actions' => 'Actiuni',
    'pending_action_approve' => 'Approve',
    'pending_action_reject' => 'Reject',
    'pending_action_reject_reason_placeholder' => 'Optional reason for the rejection',

    'approved_success' => 'OAuth client approved.',
    'rejected_success' => 'OAuth client rejected.',
    'action_requires_superuser' => 'Only superusers can approve or reject OAuth clients.',
    'client_not_found' => 'That OAuth client could not be found.',

    // Reject modal.
    'reject_modal_title' => 'Reject OAuth Client',
    'reject_modal_intro' => 'Rejecting the client will block it from completing any future OAuth flow. If the client was registered by someone legitimate, add the rejection reason so they know why.',
    'reject_modal_reason_label' => 'Reason (optional)',

    // End-user-facing messages when a pending/rejected client's
    // authorize link is opened or token exchange is attempted.
    'client_not_approved_title' => 'OAuth Client Not Approved',
    'client_not_approved_name' => 'Client name:',
    'client_not_approved_redirect' => 'Redirect URI:',
    'client_pending_message' => 'This OAuth client is awaiting administrator approval. Contact your Snipe-IT administrator if you expected this app to be approved.',
    'client_rejected_message' => 'This OAuth client has been rejected by the administrator and can no longer be used.',

    // MCP consent screen callout + REST API rejection message.
    'consent_redirect_warning' => 'After you authorize, you will be redirected to this address. If you do not recognize it, do NOT continue.',
    'rest_api_rejects_mcp_only_token' => 'This OAuth token is scoped for MCP use only and cannot be used against the REST API.',

    // AlertMenu entry.
    'alert_menu_pending_count_one' => ':count OAuth client is awaiting your approval',
    'alert_menu_pending_count_many' => ':count OAuth clients are awaiting your approval',

    'dashboard_tile_label' => 'Pending OAuth Clients',

    // Email notification fired to active superusers when a new DCR-
    // registered client lands in the pending queue.
    'notify_pending_subject' => 'A new OAuth client is awaiting your approval',
    'notify_pending_greeting' => 'buna,',
    'notify_pending_intro' => 'A new OAuth client has self-registered and is waiting on your review before it can complete an OAuth flow.',
    'notify_pending_client_name' => 'Client name:',
    'notify_pending_redirect' => 'Redirect URI:',
    'notify_pending_ip' => 'Registered from IP:',
    'notify_pending_action' => 'Review pending clients',
    'notify_pending_outro' => 'If you do not recognize this client, reject it in the admin console.',

    // Email notification fired to other superusers when a peer has
    // just approved or rejected a pending client.
    'notify_action_approved_subject' => 'An OAuth client was approved',
    'notify_action_rejected_subject' => 'An OAuth client was rejected',
    'notify_action_greeting' => 'buna,',
    'notify_action_approved_intro' => ':actor approved a pending OAuth client.',
    'notify_action_rejected_intro' => ':actor rejected a pending OAuth client.',
    'notify_action_client_name' => 'Client name:',
    'notify_action_redirect' => 'Redirect URI:',
    'notify_action_reason' => 'Reason:',
    'notify_action_action' => 'Review OAuth clients',
];
