# Snipe-IT MCP Server

This directory contains the Model Context Protocol (MCP) server implementation for Snipe-IT. It exposes the Snipe-IT asset management database to AI assistants through a standard interface, allowing them to look up assets, manage users, process checkouts and check-ins, and run common IT workflows, all in plain language.

## Table of Contents

- [Connecting to an AI Service](#connecting-to-an-ai-service)
- [Authentication](#authentication)
- [Prompts](#prompts)
- [Tools Reference](#tools-reference)
  - [Assets](#assets)
  - [Users](#users)
  - [Accessories](#accessories)
  - [Components](#components)
  - [Consumables](#consumables)
  - [Licenses](#licenses)
  - [Departments](#departments)
  - [Companies](#companies)
  - [Categories](#categories)
  - [Manufacturers](#manufacturers)
  - [Suppliers](#suppliers)
  - [Status Labels](#status-labels)
  - [Locations](#locations)
  - [Asset Models](#asset-models)
  - [Depreciations](#depreciations)
  - [Groups](#groups)
  - [Maintenance](#maintenance)
  - [Activity Log](#activity-log)

---

## Connecting to an AI Service

The MCP server is available at:

```
https://your-snipeit-domain.com/mcp/snipe-it
```

It uses **OAuth 2.0** for authentication (see [Authentication](#authentication) below). Any MCP-compatible client that supports OAuth and the Streamable HTTP transport can connect to it.

### Claude Desktop

Add the server to your `claude_desktop_config.json`:

```json
{
  "mcpServers": {
    "snipe-it": {
      "url": "https://your-snipeit-domain/mcp/snipe-it"
    }
  }
}
```

Claude Desktop will initiate the OAuth flow on first connection. Once authorized, you can use tools and prompts directly
in conversation:

> "Check out asset LAPTOP-0042 to jsmith."

> "What's assigned to sarah.chen right now?"

### MCP Inspector

The [MCP Inspector](https://laravel.com/docs/12.x/mcp#mcp-inspector) is useful for exploring and testing tools before
integrating with a client:

1. Run `php artisan mcp:inspector mcp/snipe-it` in your terminal
2. Open the provided URL in your browser

### Cursor / VS Code / Other MCP Clients

Any client that supports the MCP Streamable HTTP transport and OAuth can connect using the same URL. Refer to your client's documentation for how to add a remote MCP server.

---

## Authentication

The server uses **OAuth 2.0 with dynamic client registration**. Clients that support OAuth will handle this automatically. On first connection:

1. The client discovers the OAuth server via `/.well-known/oauth-authorization-server`
2. It registers itself dynamically via `/oauth/register`
3. The user is redirected to Snipe-IT's login page to authorize access, including which capability scopes to grant
4. The client receives a bearer token and uses it for all subsequent requests

The authenticated user's Snipe-IT permissions apply. A user without `delete assets` permission cannot call `delete_asset`, for example.

### Scopes

Every MCP session needs the `mcp:use` session scope to connect. Every MCP tool additionally declares a single required capability scope. The acting token must carry both `mcp:use` AND the tool's exact capability scope, or the call is refused. This lets an administrator issue, say, a read-only AI connector token to a user who otherwise has full write permissions in the system.

| Scope | Layer | What it unlocks |
|---|---|---|
| `mcp:use` | Session | Permission to open an MCP session at all. The route's `mcp.scope.handshake` middleware rejects tokens without it at HTTP 401. A token carrying ONLY `mcp:use` passes the handshake but cannot call any tool. |
| `mcp:read` | Capability | All `list_*`, `show_*`, `get_current_user`, `get_user_assets`, `show_user_permissions`, `find_similar_models`, `list_history`, `list_uploads`, `list_asset_notes`, `list_maintenances`, `list_maintenance_types` |
| `mcp:write` | Capability | All `create_*`, `update_*`, `update_profile`, `audit_asset`, `add_asset_note`, `upload_asset_image`, `reset_2fa`, `send_password_reset` |
| `mcp:delete` | Capability | All `delete_*`, `restore_asset`, `restore_user` |
| `mcp:checkout` | Capability | `checkout_asset`, `checkout_accessory`, `checkout_component`, `checkout_consumable`, `checkout_license`, `offboard_user`, `transfer_user_assets` |
| `mcp:checkin` | Capability | `checkin_asset`, `checkin_accessory`, `checkin_component`, `checkin_license` |
| `mcp:superadmin` | Capability | All group management (`list_groups`, `show_group`, `create_group`, `update_group`, `delete_group`) |

Semantics:

- **Session gate is separate.** `mcp:use` only gets the token past the connection handshake. On its own it calls nothing. Tokens issued pre-scope (before this release) hit the handshake gate and reauthorize from there.
- **Exact capability match.** A token with `mcp:write` cannot call a read tool, and vice versa. There is no superset relationship between capability scopes. A token with `mcp:superadmin` does NOT implicitly grant any other capability.
- **Both layers enforce.** The scope check runs first. If it passes, the tool's existing Snipe-IT permission check (via `Gate::allows`) still runs. Both must pass for the tool to execute.
- **Grant what's needed.** During OAuth consent, grant `mcp:use` plus only the capability scopes the client will use. For a read-only reporting assistant that's `mcp:use` + `mcp:read`. For a full-capability admin tool it may be the whole set.
- **Why the split.** Some MCP clients (notably ChatGPT's connector) only request a single hardcoded scope. `mcp:use` is what those clients request, which is why it exists as a session-only gate rather than being collapsed into the capability scopes.

Scope errors return the generic `scope_denied` message and do not reveal which scope was missing, to the extent a wrong-scope diff would otherwise fingerprint the caller's capability set.

### Route middleware chain

Every request to `/mcp/snipe-it` passes through this chain, in order:

| Middleware | Response on failure | Purpose |
|---|---|---|
| `auth:api` | HTTP 401 | Validates the Passport bearer token. Attaches the user to the request. Without a valid token, the request is rejected here. |
| `CheckUserIsActivated` | HTTP 401 | Rejects deactivated users whose tokens would otherwise keep working. Matches the REST API's behavior so an admin deactivating a user terminates both their web session AND their MCP session. |
| `EnforceApiTwoFactorEnrollment` | HTTP 403 | On installs that require 2FA (`two_factor_enabled=2`), rejects users who have not enrolled. Prevents 2FA-required orgs from being bypassed via MCP. |
| `mcp.scope.handshake` | HTTP 401 + `WWW-Authenticate: Bearer error="insufficient_scope", scope="..."` | Rejects tokens that carry zero `mcp:*` scopes. The WWW-Authenticate header advertises all available MCP scopes so OAuth-compliant clients can re-initiate authorization. This is the gate pre-scope tokens (issued before this release) hit. |
| `api-throttle:api` | HTTP 429 | Rate limiting plus JSON response-header setup. |

Per-tool scope checks (via `HasMcpScope`) run **inside** the MCP tool handler, so their errors come back as JSON-RPC-level tool errors inside an HTTP 200 response. Session-level failures (the four middleware above) short-circuit at the HTTP layer with the appropriate status + headers, which is what compliant OAuth clients need to trigger a reauthorization flow.

---

## Prompts

Prompts are pre-built conversation starters for common multi-step workflows. In clients that support them (such as Claude Desktop), prompts appear in a slash-command menu or prompt picker. Select a prompt, fill in any arguments, and the AI will walk through the workflow using the available tools automatically.

---

### `onboard_employee`

**Guide through creating a new employee account and assigning appropriate equipment and licenses.**

Walks the AI through a nine-step onboarding flow:

1. Inspect existing users to infer the install's username convention (firstname.lastname, firstinitial+last, employee
   number, email-style, etc) and propose a matching username for the admin to confirm.
2. Ask for an email address if one was not supplied and whether to send a password reset link.
3. Create the user account with the confirmed username. On username conflict, propose a disambiguated variant and retry
   after re-confirmation.
4. Send the password reset email if the admin asked for one.
5. Search for available (undeployed) assets suitable for the role.
6. Check the selected assets out to the new user.
7. Check for license seats that should be assigned and assign them.
8. Verify by re-running the user lookup: account exists (not soft-deleted), every intended asset is assigned, every
   intended license seat is assigned. The `activated` flag is explicitly NOT a "still with the company" signal since
   many installs never set it on real users.
9. Summarise what was set up, including the verification result.

| Argument | Required | Description |
|----------|----------|-------------|
| `first_name` | Yes | First name of the new employee |
| `last_name` | No | Last name of the new employee |
| `department` | No | Department the employee will join |
| `location` | No | Primary office location |
| `title` | No | Job title |

Username is intentionally NOT a prompt argument. It is negotiated conversationally in step 1 so the AI can match the
install's existing pattern and the admin stays in control of the final value.

**Example usage in Claude:**
> Use the `onboard_employee` prompt → first_name: "Marcus", last_name: "Webb", department: "Engineering", location: "Austin HQ"

---

### `offboard_employee`

**Check in all equipment and licenses from a departing employee, clear downstream manager references, and soft-delete
their account.**

Thin wrapper around the atomic `offboard_user` tool. The AI calls the tool with `dry_run=true` first, presents the
preview (every asset, accessory, license seat, managed user, managed location that would change, plus the soft-delete),
waits for admin confirmation, then calls the tool again with `dry_run=false` to execute everything in a single DB
transaction.

| Argument | Required | Description |
|----------|----------|-------------|
| `username` | Yes | Username of the departing employee |

**Example usage in Claude:**
> Use the `offboard_employee` prompt → username: "marcus.webb"

---

### `transfer_employee_assets`

**Transfer every asset, accessory, and license seat from one employee to another.**

Thin wrapper around the atomic `transfer_user_assets` tool. Common when an employee changes teams or roles. The AI calls
the tool with `dry_run=true` first, presents the preview of what would move, waits for admin confirmation, then calls it
again with `dry_run=false` to execute the whole transfer in a single DB transaction. Consumables are not transferred
because consumable checkouts are one-way (they do not return to stock).

| Argument        | Required | Description                     |
|-----------------|----------|---------------------------------|
| `from_username` | Yes      | Username of the source employee |
| `to_username`   | Yes      | Username of the target employee |

**Example usage in Claude:**
> Use the `transfer_employee_assets` prompt → from_username: "marcus.webb", to_username: "sarah.chen"

---

### `audit_location`

**Review all assets at a location, flag overdue audits and status anomalies.**

Lists all assets at the location, identifies overdue audit dates, flags unexpected statuses, and produces a summary with recommended actions.

| Argument | Required | Description |
|----------|----------|-------------|
| `location` | Yes | Name or ID of the location to audit |

**Example usage in Claude:**
> Use the `audit_location` prompt → location: "Austin HQ"

---

### `find_available_asset`

**Find an undeployed asset by category or model and optionally check it out to a user.**

Searches for Ready-to-Deploy assets matching the criteria, lists options if multiple are available, and can check out the selected asset immediately.

| Argument | Required | Description |
|----------|----------|-------------|
| `category` | No | Asset category to search (e.g. Laptop, Monitor) |
| `model` | No | Specific model name to search for |
| `assign_to` | No | Username to check the asset out to once found |

**Example usage in Claude:**
> Use the `find_available_asset` prompt → category: "Laptop", assign_to: "marcus.webb"

---

### `expiring_licenses`

**Review license seat usage and flag licenses expiring within a given number of days.**

Lists all licenses, identifies those expiring soon, flags over-deployed and under-used licenses, and produces a prioritised action list.

| Argument | Required | Description |
|----------|----------|-------------|
| `days` | No | Days ahead to check for expiry (default: 30) |

**Example usage in Claude:**
> Use the `expiring_licenses` prompt → days: 60

---

### `end_of_life_review`

**Identify assets that have passed their EOL date or are fully depreciated, and recommend disposition actions.**

Can be scoped to a department or category. Groups findings and recommends retirement, redeployment, repair, or archival.

| Argument | Required | Description |
|----------|----------|-------------|
| `department` | No | Limit review to a specific department |
| `category` | No | Limit review to a specific asset category |

**Example usage in Claude:**
> Use the `end_of_life_review` prompt → category: "Laptop"

---

### `warranty_expiring`

**List assets whose warranty expires within a given number of days.**

Groups findings by urgency (within 30 days, 31–60, 61–N), flags assets in critical roles, and recommends extensions or replacements.

| Argument | Required | Description |
|----------|----------|-------------|
| `days` | No | Days ahead to check for warranty expiry (default: 90) |

**Example usage in Claude:**
> Use the `warranty_expiring` prompt → days: 30

---

### `inventory_summary`

**Produce a high-level inventory count by category, broken down by deployment status.**

Can be scoped to a location or department. Shows deployed vs. available counts, top models, total value, and stock-out risks.

| Argument | Required | Description |
|----------|----------|-------------|
| `location` | No | Limit report to a specific location |
| `department` | No | Limit report to a specific department |

**Example usage in Claude:**
> Use the `inventory_summary` prompt → location: "Austin HQ"

---

### `user_inventory`

**List everything currently assigned to a specific user across all asset types.**

Shows assets, accessories, license seats, and consumables assigned to the user, plus total assigned value if cost data is available.

| Argument | Required | Description |
|----------|----------|-------------|
| `username` | Yes | Username of the user to review |

**Example usage in Claude:**
> Use the `user_inventory` prompt → username: "sarah.chen"

---

### `bulk_asset_replacement`

**Find every current holder of a given asset model and propose a per-user replacement plan.**

Planning-only. Lists every deployed unit of a model, shows available replacement inventory, computes the gap, and
proposes per-user replacement assignments prioritised by EOL / out-of-warranty first. Does not execute any check-ins or
check-outs.

| Argument    | Required | Description                                      |
|-------------|----------|--------------------------------------------------|
| `old_model` | Yes      | Name or ID of the asset model being replaced     |
| `new_model` | No       | Replacement model, if a specific one is targeted |

**Example usage in Claude:**
> Use the `bulk_asset_replacement` prompt → old_model: "MacBook Pro 13-inch 2020", new_model: "MacBook Pro 14-inch 2024"

---

### `location_shutdown`

**Plan the shutdown of a physical location by inventorying every asset and user tied to it.**

Planning-only. Lists assets whose current or default location is the closing site, lists users at that location,
surfaces any child locations in the hierarchy, and proposes a disposition plan per asset / user. Does not update any
records.

| Argument   | Required | Description                                |
|------------|----------|--------------------------------------------|
| `location` | Yes      | Name or ID of the location being shut down |

**Example usage in Claude:**
> Use the `location_shutdown` prompt → location: "London Office"

---

### `report_lost_stolen`

**Document circumstances of a lost or stolen asset, update its status, and note the last known assignee.**

Captures the current assignee (or most recent one from history), records the circumstances as an asset note, updates the
status label to Lost or Stolen, and produces an insurance / security summary. The assignment is intentionally left in
place so accountability stays visible in the history.

| Argument        | Required | Description                           |
|-----------------|----------|---------------------------------------|
| `asset_tag`     | Yes      | Asset tag of the lost or stolen asset |
| `disposition`   | No       | Either "lost" or "stolen"             |
| `circumstances` | No       | Short description of what happened    |

**Example usage in Claude:**
> Use the `report_lost_stolen` prompt → asset_tag: "AST-00142", disposition: "stolen", circumstances: "Taken from car in
> parking garage, police report filed"

---

### `capacity_check`

**Check whether current Ready-to-Deploy inventory can cover a projected demand.**

Planning-only. Counts available inventory in the requested category or model, compares to projected demand, reports the
gap, and suggests reorder candidates based on historical suppliers.

| Argument   | Required | Description                             |
|------------|----------|-----------------------------------------|
| `count`    | Yes      | Number of units needed                  |
| `category` | No       | Asset category to check (e.g. "Laptop") |
| `model`    | No       | Specific asset model to check           |

**Example usage in Claude:**
> Use the `capacity_check` prompt → count: 10, category: "Laptop"

---

### `low_stock_alert`

**List accessories and consumables at or below their configured minimum quantity.**

Planning-only. Enumerates items whose current remaining quantity is at or below `min_amt`, groups by supplier for easier
reordering, and suggests a reorder quantity. Items at zero stock are flagged as urgent.

No arguments.

**Example usage in Claude:**
> Use the `low_stock_alert` prompt

---

### `data_quality_audit`

**Scan the database for common data quality problems across assets, users, and licenses.**

Read-only report. Runs thirteen integrity checks (missing or duplicate serials, missing purchase data,
status-vs-assignee mismatches, orphan assignments, soft-deleted users with assigned seats, etc), groups findings by
severity, and suggests remediation steps. Does not modify any records.

No arguments.

**Example usage in Claude:**
> Use the `data_quality_audit` prompt

---

### `vendor_summary`

**Produce a procurement-focused summary of everything acquired from a given supplier or manufacturer.**

Read-only report. Aggregates assets, licenses, consumables, accessories, and maintenance history tied to one vendor.
Reports inventory counts (with status breakdown), total spend across all asset types, warranty coverage for deployed
assets, open vs completed service tickets, and flags low-stock / fully-consumed / expiring items that may need
attention.

| Argument      | Required | Description                                             |
|---------------|----------|---------------------------------------------------------|
| `vendor_name` | Yes      | Name of the supplier or manufacturer to summarise       |
| `vendor_type` | No       | Either "supplier" or "manufacturer" (default: supplier) |

**Example usage in Claude:**
> Use the `vendor_summary` prompt → vendor_name: "Dell", vendor_type: "manufacturer"

---

## Tools Reference

Tools are individual actions the AI can call directly. They can also be combined freely in conversation without using a prompt, just describe what you want in plain language.

> "Find the MacBook Pro with serial C02XL0AAJGH5 and check it in."

> "Create a new location called 'Denver Office' in Colorado."

> "List all licenses expiring before the end of the year."

---

### Assets

#### `show_asset`
Look up a single asset by asset tag, serial number, or numeric ID.

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `asset_tag` | string | No | Asset tag |
| `serial` | string | No | Serial number |
| `id` | number | No | Numeric ID |

#### `list_assets`

Search and list assets with optional filters. When `filter` and `search` are both provided, `filter` wins.

| Parameter         | Type   | Required | Description                                                                                                                                                                                          |
|-------------------|--------|----------|------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `search`          | string | No       | Free-text keyword across tag, serial, name, model                                                                                                                                                    |
| `filter`          | object | No       | Structured advanced-search payload (same shape the UI Advanced Search modal posts). Supports date ranges, enum lists, custom-field filters by db_column, and any column in `Asset::$allowed_columns` |
| `status_type`     | string | No       | `RTD`, `Deployed`, `Archived`, `Pending`, or `Undeployable`                                                                                                                                          |
| `company_id`      | number | No       | Filter by company                                                                                                                                                                                    |
| `location_id`     | number | No       | Filter by current location                                                                                                                                                                           |
| `rtd_location_id` | number | No       | Filter by default (Ready to Deploy) location                                                                                                                                                         |
| `category_id`     | number | No       | Filter by category                                                                                                                                                                                   |
| `model_id`        | number | No       | Filter by model                                                                                                                                                                                      |
| `manufacturer_id` | number | No       | Filter by manufacturer                                                                                                                                                                               |
| `supplier_id`     | number | No       | Filter by supplier                                                                                                                                                                                   |
| `limit`           | number | No       | Results to return (default: 25, max: 500)                                                                                                                                                            |
| `offset`          | number | No       | Results to skip for pagination                                                                                                                                                                       |

Response rows include lifecycle fields (`purchase_date`, `purchase_cost`, `warranty_months`, `warranty_expires`,
`asset_eol_date`), last-* timestamps (`last_checkout`, `last_checkin`, `last_audit_date`, `next_audit_date`), child-item
counts (`licenses_count`, `components_count`, `maintenances_count`, `assigned_assets_count`), and `notes`.

#### `create_asset`
Create a new asset.

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `model_id` | number | **Yes** | Asset model ID |
| `status_id` | number | **Yes** | Status label ID |
| `asset_tag` | string | **Yes** | Asset tag |
| `name` | string | No | Display name |
| `serial` | string | No | Serial number |
| `company_id` | number | No | Company ID |
| `location_id` | number | No | Current location ID |
| `rtd_location_id` | number | No | Default RTD location ID |
| `supplier_id` | number | No | Supplier ID |
| `purchase_date` | string | No | Purchase date (YYYY-MM-DD) |
| `purchase_cost` | number | No | Purchase cost |
| `order_number` | string | No | Order number |
| `warranty_months` | number | No | Warranty length in months (0–240) |
| `requestable` | boolean | No | Whether users can request this asset |
| `notes` | string | No | Notes |

#### `update_asset`
Update fields on an asset. Identify it by `asset_tag`, `serial`, or `id`.

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `asset_tag` | string | No | Identify by asset tag |
| `serial` | string | No | Identify by serial number |
| `id` | number | No | Identify by numeric ID |
| `name` | string | No | New display name |
| `new_asset_tag` | string | No | Rename the asset tag |
| `new_serial` | string | No | New serial number |
| `status_id` | number | No | Status label ID |
| `model_id` | number | No | Model ID |
| `notes` | string | No | Notes |
| `order_number` | string | No | Order number |
| `purchase_date` | string | No | Purchase date (YYYY-MM-DD) |
| `purchase_cost` | number | No | Purchase cost |
| `warranty_months` | number | No | Warranty length in months |
| `location_id` | number | No | Current location ID |
| `rtd_location_id` | number | No | Default RTD location ID |
| `supplier_id` | number | No | Supplier ID |
| `requestable` | boolean | No | User-requestable flag |
| `byod` | boolean | No | Bring-your-own-device flag |
| `asset_eol_date` | string | No | End-of-life date (YYYY-MM-DD) |
| `expected_checkin` | string | No | Expected check-in date (YYYY-MM-DD) |
| `next_audit_date` | string | No | Next audit date (YYYY-MM-DD) |

#### `delete_asset`
Soft-delete an asset. If checked out, it will be checked in first.

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `asset_tag` | string | No | Identify by asset tag |
| `serial` | string | No | Identify by serial number |
| `id` | number | No | Identify by numeric ID |

#### `restore_asset`
Restore a soft-deleted asset.

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | number | **Yes** | Numeric ID of the asset to restore |

#### `checkout_asset`
Check out an asset to a user, location, or another asset.

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `asset_tag` | string | No | Identify asset by tag |
| `id` | number | No | Identify asset by numeric ID |
| `checkout_to_type` | string | **Yes** | `user`, `location`, or `asset` |
| `assigned_user` | number | No | User ID (when checking out to a user) |
| `assigned_location` | number | No | Location ID (when checking out to a location) |
| `assigned_asset` | number | No | Asset ID (when checking out to an asset) |
| `note` | string | No | Checkout note |
| `checkout_at` | string | No | Checkout date (YYYY-MM-DD, defaults to now) |
| `expected_checkin` | string | No | Expected check-in date (YYYY-MM-DD) |

#### `checkin_asset`
Check a currently checked-out asset back in.

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `asset_tag` | string | No | Identify by asset tag |
| `id` | number | No | Identify by numeric ID |
| `note` | string | No | Check-in note |

#### `audit_asset`
Record an audit for an asset, updating the last audit date and optionally the location.

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `asset_tag` | string | No | Identify by asset tag |
| `serial` | string | No | Identify by serial number |
| `id` | number | No | Identify by numeric ID |
| `note` | string | No | Audit note |
| `location_id` | number | No | Location where the asset was found |
| `next_audit_date` | string | No | Override the next audit date (YYYY-MM-DD) |

#### `add_asset_note`
Add a manual note to an asset. The note is recorded in the asset's activity log.

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `asset_tag` | string | No | Identify by asset tag |
| `serial` | string | No | Identify by serial number |
| `id` | number | No | Identify by numeric ID |
| `note` | string | **Yes** | Note text to add |

---

### Users

#### `list_users`

Search and list users. When `filter` and `search` are both provided, `filter` wins.

| Parameter         | Type    | Required | Description                                                                                                                                |
|-------------------|---------|----------|--------------------------------------------------------------------------------------------------------------------------------------------|
| `search`          | string  | No       | Free-text keyword across name, username, email, employee number                                                                            |
| `filter`          | object  | No       | Structured advanced-search filter payload                                                                                                  |
| `company_id`      | number  | No       | Filter by company                                                                                                                          |
| `department_id`   | number  | No       | Filter by department                                                                                                                       |
| `location_id`     | number  | No       | Filter by location                                                                                                                         |
| `activated`       | boolean | No       | Filter by the login-access flag. Not a reliable "still with the company" signal since many orgs never set it. Use `only_deleted` for that. |
| `include_deleted` | boolean | No       | Include soft-deleted users in results (default: false)                                                                                     |
| `only_deleted`    | boolean | No       | Return ONLY soft-deleted users. Takes precedence over `include_deleted`.                                                                   |
| `limit`           | number  | No       | Results to return (default: 25, max: 500)                                                                                                  |
| `offset`          | number  | No       | Results to skip for pagination                                                                                                             |

Response rows include `employee_num`, `deleted_at`, `last_login`, `start_date`, `end_date`, child-item counts
(`assets_count`, `licenses_count`, `accessories_count`, `consumables_count`), and `notes`.

#### `show_user`
Look up a single user by numeric ID, username, or email address.

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | number | No | Numeric user ID |
| `username` | string | No | Username |
| `email` | string | No | Email address |

#### `show_user_permissions`
Return the effective permission set for a user, merging their individual grants and denials with permissions inherited from group membership. Omit all identifiers to query the currently authenticated user. Self-lookups bypass the `users.view` gate. Querying another user's permissions requires `users.view` on the target.

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | number | No | Numeric user ID (omit to query the acting user) |
| `username` | string | No | Username |
| `email` | string | No | Email address |

Response structure:

| Field | Type | Description |
|-------|------|-------------|
| `id` | number | Numeric user ID |
| `username` | string | Username |
| `is_superuser` | boolean | True when the user holds the superuser permission (implicitly passes every authorization check) |
| `groups` | array | Permission groups the user belongs to (`id`, `name`) |
| `allowed_permissions` | array | Flat list of permission keys the user can perform (e.g. `assets.view`, `licenses.checkout`) after merging individual + group grants and denials |
| `permissions_by_section` | object | Permissions grouped by section. Each row carries `status` (allowed/denied), `source` (individual/group/superuser), and the group names that contributed the grant or denial |

#### `create_user`
Create a new user account.

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `first_name` | string | **Yes** | First name |
| `username` | string | **Yes** | Username (must be unique) |
| `last_name` | string | No | Last name |
| `email` | string | No | Email address |
| `password` | string | No | Password (min 8 characters) |
| `employee_num` | string | No | Employee number |
| `jobtitle` | string | No | Job title |
| `phone` | string | No | Phone number |
| `company_id` | number | No | Company ID |
| `department_id` | number | No | Department ID |
| `location_id` | number | No | Location ID |
| `manager_id` | number | No | Manager user ID |
| `activated` | boolean | No | Whether the account is active (default: true) |
| `notes` | string | No | Notes |
| `start_date` | string | No | Employment start date (YYYY-MM-DD) |
| `end_date` | string | No | Employment end date (YYYY-MM-DD) |
| `vip` | boolean | No | Mark as VIP |
| `remote` | boolean | No | Mark as remote worker |
| `address` | string | No | Street address |
| `city` | string | No | City |
| `state` | string | No | State/province |
| `country` | string | No | Country |
| `zip` | string | No | Postal/ZIP code |

#### `update_user`
Update fields on a user. Identify by `id`, `username`, or `email`. Use `new_username` or `new_email` to change those values.

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | number | No | Identify by numeric ID |
| `username` | string | No | Identify by username |
| `email` | string | No | Identify by email |
| `new_username` | string | No | Rename the username |
| `new_email` | string | No | New email address |
| `first_name` | string | No | First name |
| `last_name` | string | No | Last name |
| `password` | string | No | New password (min 8 characters) |
| `employee_num` | string | No | Employee number |
| `jobtitle` | string | No | Job title |
| `phone` | string | No | Phone number |
| `company_id` | number | No | Company ID |
| `department_id` | number | No | Department ID |
| `location_id` | number | No | Location ID |
| `manager_id` | number | No | Manager user ID |
| `activated` | boolean | No | Account active status |
| `notes` | string | No | Notes |
| `start_date` | string | No | Employment start date (YYYY-MM-DD) |
| `end_date` | string | No | Employment end date (YYYY-MM-DD) |
| `vip` | boolean | No | VIP flag |
| `remote` | boolean | No | Remote worker flag |

#### `delete_user`
Soft-delete a user. The user must have no assets, licenses, accessories, or consumables assigned.

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | number | No | Numeric user ID |
| `username` | string | No | Username |
| `email` | string | No | Email address |

#### `restore_user`
Restore a soft-deleted user.

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | number | **Yes** | Numeric ID of the user to restore |

#### `get_current_user`
Return information about the currently authenticated user. No parameters.

#### `update_profile`
Update the authenticated user's own profile. Fields protected by the `self.profile` gate (`first_name`, `last_name`, `phone`, `website`, `gravatar`) require profile editing to be enabled in Snipe-IT settings. `location_id` requires the `self.edit_location` permission.

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `first_name` | string | No | First name |
| `last_name` | string | No | Last name |
| `phone` | string | No | Phone number |
| `website` | string | No | Personal website URL |
| `gravatar` | string | No | Gravatar email or hash |
| `locale` | string | No | Locale/language code (e.g. `en-US`) |
| `two_factor_optin` | boolean | No | Opt in to 2FA (requires `self.two_factor` permission and 2FA enabled in settings) |
| `location_id` | number | No | Default location ID (requires `self.edit_location` permission) |

#### `get_user_assets`
Return all assets currently checked out to a user.

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | number | **Yes** | Numeric user ID |

#### `reset_2fa`
Reset two-factor authentication for a user (requires admin permission).

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | number | **Yes** | Numeric user ID |

#### `offboard_user`

Atomic offboarding for a single user. Previews or executes check-in of every assigned asset, accessory, and license
seat, clears manager references on dependent users and locations, and soft-deletes the account. Defaults to
`dry_run=true` so no changes are made without explicit confirmation. All writes happen inside one DB transaction so
partial failures roll back.

| Parameter    | Type    | Required | Description                                                                                                                                            |
|--------------|---------|----------|--------------------------------------------------------------------------------------------------------------------------------------------------------|
| `username`   | string  | No       | Username of the user to offboard (preferred because unique). One of `username` or `first_name`+`last_name` is required.                                |
| `first_name` | string  | No       | First name. Combine with `last_name` when username is unknown. If multiple users match, the tool returns the match set for the caller to disambiguate. |
| `last_name`  | string  | No       | Last name.                                                                                                                                             |
| `dry_run`    | boolean | No       | When `true` (default) return the preview without making changes. Set `false` to execute.                                                               |
| `note`       | string  | No       | Optional note recorded on each asset checkin.                                                                                                          |

#### `transfer_user_assets`

Atomic user-to-user transfer of every asset, accessory, and license seat. Defaults to `dry_run=true`. Executes all moves
in a single DB transaction. Consumables are not transferred (consumable checkouts are one-way).

| Parameter         | Type    | Required | Description                                                                                            |
|-------------------|---------|----------|--------------------------------------------------------------------------------------------------------|
| `from_username`   | string  | No       | Username of the source user. One of `from_username` or `from_first_name`+`from_last_name` is required. |
| `from_first_name` | string  | No       | First name of the source user.                                                                         |
| `from_last_name`  | string  | No       | Last name of the source user.                                                                          |
| `to_username`     | string  | No       | Username of the target user. Same rules as `from_username`.                                            |
| `to_first_name`   | string  | No       | First name of the target user.                                                                         |
| `to_last_name`    | string  | No       | Last name of the target user.                                                                          |
| `dry_run`         | boolean | No       | When `true` (default) return the preview without making changes. Set `false` to execute.               |
| `note`            | string  | No       | Optional note recorded on each asset checkin and checkout.                                             |

---

### Accessories

#### `list_accessories`

Search and list accessories. When `filter` and `search` are both provided, `filter` wins.

| Parameter         | Type    | Required | Description                                                                                         |
|-------------------|---------|----------|-----------------------------------------------------------------------------------------------------|
| `search`          | string  | No       | Free-text keyword across accessory name and model number                                            |
| `filter`          | object  | No       | Structured advanced-search filter payload                                                           |
| `company_id`      | number  | No       | Filter by company                                                                                   |
| `category_id`     | number  | No       | Filter by category                                                                                  |
| `manufacturer_id` | number  | No       | Filter by manufacturer                                                                              |
| `supplier_id`     | number  | No       | Filter by supplier                                                                                  |
| `location_id`     | number  | No       | Filter by location                                                                                  |
| `low_stock_only`  | boolean | No       | Return only accessories whose remaining quantity is at or below `min_amt`. Archived accessories are always excluded from this filter.  |
| `include_archived`| boolean | No       | Include archived accessories in results. Default matches `accessoriesForShow()`: archived rows are hidden unless `show_archived_in_list` is on. |
| `archived_only`   | boolean | No       | Return ONLY archived accessories. Overrides `include_archived`.                                     |
| `limit`           | number  | No       | Results to return (default: 25, max: 500)                                                           |
| `offset`          | number  | No       | Results to skip for pagination                                                                      |

Response rows include `qty`, `min_amt`, `remaining` (computed), `supplier`, `purchase_cost`, `purchase_date`,
`order_number`, `archived`, and `notes`.

#### `create_accessory`

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `name` | string | **Yes** | Accessory name |
| `category_id` | number | **Yes** | Category ID (must be an accessory category) |
| `qty` | number | No | Total quantity in stock |
| `model_number` | string | No | Model number |
| `manufacturer_id` | number | No | Manufacturer ID |
| `supplier_id` | number | No | Supplier ID |
| `location_id` | number | No | Location ID |
| `company_id` | number | No | Company ID |
| `order_number` | string | No | Order number |
| `purchase_cost` | number | No | Purchase cost per unit |
| `purchase_date` | string | No | Purchase date (YYYY-MM-DD) |
| `min_amt` | number | No | Minimum quantity alert threshold |
| `requestable` | boolean | No | User-requestable flag. Mutually exclusive with `archived`. |
| `archived` | boolean | No | Archive flag. Archived accessories cannot be checked out, are hidden from the default list, and are excluded from low-stock alerts. Mutually exclusive with `requestable`. |
| `notes` | string | No | Notes |

#### `update_accessory`
Identify by `id` or `name`. Use `new_name` to rename. Pass `archived=true` to archive (fails if units are still checked out) or `archived=false` to unarchive.

#### `delete_accessory`
The accessory must have no units currently checked out. Identify by `id` or `name`.

#### `checkout_accessory`
Refuses to check out archived accessories. Unarchive via `update_accessory` first.

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | number | No | Identify by numeric ID |
| `name` | string | No | Identify by name |
| `checkout_to_type` | string | **Yes** | `user`, `location`, or `asset` |
| `assigned_user` | number | No | User ID to check out to |
| `assigned_location` | number | No | Location ID to check out to |
| `assigned_asset` | number | No | Asset ID to check out to |
| `note` | string | No | Checkout note |

#### `checkin_accessory`

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `checkout_id` | number | **Yes** | ID of the checkout record to check in |
| `note` | string | No | Check-in note |

---

### Components

#### `create_component`

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `name` | string | **Yes** | Component name |
| `category_id` | number | **Yes** | Category ID (must be a component category) |
| `qty` | number | **Yes** | Total quantity in stock (min 1) |
| `serial` | string | No | Serial number |
| `model_number` | string | No | Model number |
| `manufacturer_id` | number | No | Manufacturer ID |
| `supplier_id` | number | No | Supplier ID |
| `location_id` | number | No | Location ID |
| `company_id` | number | No | Company ID |
| `order_number` | string | No | Order number |
| `purchase_cost` | number | No | Purchase cost per unit |
| `purchase_date` | string | No | Purchase date (YYYY-MM-DD) |
| `min_amt` | number | No | Minimum quantity alert threshold |
| `archived` | boolean | No | Archive flag. Archived components cannot be checked out and are hidden from the default list. Cannot be archived while units are checked out. |
| `notes` | string | No | Notes |

#### `update_component`
Identify by `id` or `name`. Use `new_name` to rename. Pass `archived=true` to archive (fails if units are still checked out to assets) or `archived=false` to unarchive.

#### `delete_component`
The component must have no units checked out to assets. Identify by `id` or `name`.

#### `checkout_component`
Refuses to check out archived components. Unarchive via `update_component` first.

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | number | No | Identify by numeric ID |
| `name` | string | No | Identify by name |
| `asset_id` | number | **Yes** | Asset ID to check the component out to |
| `assigned_qty` | number | No | Number of units to check out (default: 1) |
| `note` | string | No | Checkout note |

#### `checkin_component`

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `component_asset_id` | number | **Yes** | ID of the checkout record to check in |
| `checkin_qty` | number | No | Units to check in (default: all) |
| `note` | string | No | Check-in note |

---

### Consumables

#### `list_consumables`

Search and list consumables. When `filter` and `search` are both provided, `filter` wins.

| Parameter         | Type    | Required | Description                                                                                         |
|-------------------|---------|----------|-----------------------------------------------------------------------------------------------------|
| `search`          | string  | No       | Free-text keyword                                                                                   |
| `filter`          | object  | No       | Structured advanced-search filter payload                                                           |
| `company_id`      | number  | No       | Filter by company                                                                                   |
| `category_id`     | number  | No       | Filter by category                                                                                  |
| `manufacturer_id` | number  | No       | Filter by manufacturer                                                                              |
| `location_id`     | number  | No       | Filter by location                                                                                  |
| `low_stock_only`  | boolean | No       | Return only consumables whose remaining quantity is at or below `min_amt`. Archived consumables are always excluded from this filter. |
| `include_archived`| boolean | No       | Include archived consumables in results. Default matches `consumablesForShow()`: archived rows are hidden unless `show_archived_in_list` is on. |
| `archived_only`   | boolean | No       | Return ONLY archived consumables. Overrides `include_archived`.                                     |
| `limit`           | number  | No       | Results to return (default: 25, max: 500)                                                           |
| `offset`          | number  | No       | Results to skip                                                                                     |

Response rows include `qty`, `min_amt`, `remaining` (computed), `supplier`, `model_number`, `item_no`, `archived`, and `notes`.

#### `show_consumable`
Look up by `id` or `name`. Response includes `archived` and `requestable` flags.

#### `create_consumable`

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `name` | string | **Yes** | Consumable name |
| `qty` | number | **Yes** | Quantity in stock |
| `category_id` | number | **Yes** | Category ID (must be a consumable category) |
| `company_id` | number | No | Company ID |
| `location_id` | number | No | Location ID |
| `manufacturer_id` | number | No | Manufacturer ID |
| `supplier_id` | number | No | Supplier ID |
| `item_no` | string | No | Item number |
| `order_number` | string | No | Order number |
| `model_number` | string | No | Model number |
| `purchase_cost` | number | No | Purchase cost per unit |
| `purchase_date` | string | No | Purchase date (YYYY-MM-DD) |
| `min_amt` | number | No | Minimum quantity alert threshold |
| `requestable` | boolean | No | User-requestable flag. Mutually exclusive with `archived`. |
| `archived` | boolean | No | Archive flag. Archived consumables cannot be checked out, are hidden from the default list, and are excluded from low-stock alerts. Mutually exclusive with `requestable`. |
| `notes` | string | No | Notes |

#### `update_consumable`
Identify by `id` or `name`. Use `new_name` to rename. Pass `archived=true` to archive (fails if outstanding distributions remain) or `archived=false` to unarchive.

#### `delete_consumable`
The consumable must have no units currently checked out. Identify by `id` or `name`.

#### `checkout_consumable`
Refuses to check out archived consumables. Unarchive via `update_consumable` first.

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | number | No | Identify by numeric ID |
| `name` | string | No | Identify by name |
| `assigned_to` | number | **Yes** | User ID to check out to |
| `note` | string | No | Checkout note |

---

### Licenses

#### `list_licenses`

Search and list licenses. When `filter` and `search` are both provided, `filter` wins.

| Parameter              | Type    | Required | Description                                                                                                      |
|------------------------|---------|----------|------------------------------------------------------------------------------------------------------------------|
| `search`               | string  | No       | Free-text keyword across name, serial, notes, order number                                                       |
| `filter`               | object  | No       | Structured advanced-search filter payload                                                                        |
| `company_id`           | number  | No       | Filter by company                                                                                                |
| `category_id`          | number  | No       | Filter by category                                                                                               |
| `manufacturer_id`      | number  | No       | Filter by manufacturer                                                                                           |
| `supplier_id`          | number  | No       | Filter by supplier                                                                                               |
| `depreciation_id`      | number  | No       | Filter by depreciation schedule                                                                                  |
| `expiring_within_days` | number  | No       | Return only licenses whose `expiration_date` is between today and (today + N days). Use for renewal-due reports. |
| `fully_consumed_only`  | boolean | No       | Return only licenses with zero free seats remaining.                                                             |
| `limit`                | number  | No       | Results to return (default: 25, max: 500)                                                                        |
| `offset`               | number  | No       | Results to skip                                                                                                  |

Response rows include `seats`, `free_seats`, `depreciation`, `license_name`, `license_email`, `maintained`,
`reassignable`, `expiration_date`, `termination_date`, `purchase_cost`, `min_amt`, and `notes`.

#### `show_license`
Look up by `id` or `name`. Returns seat counts.

#### `create_license`

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `name` | string | **Yes** | License name |
| `seats` | number | **Yes** | Number of seats (min 1) |
| `category_id` | number | **Yes** | Category ID (must be a license category) |
| `serial` | string | No | Product key / serial number |
| `manufacturer_id` | number | No | Manufacturer ID |
| `supplier_id` | number | No | Supplier ID |
| `company_id` | number | No | Company ID |
| `purchase_date` | string | No | Purchase date (YYYY-MM-DD) |
| `purchase_cost` | number | No | Purchase cost |
| `expiration_date` | string | No | Expiration date (YYYY-MM-DD) |
| `license_name` | string | No | Name of the licensed user/organisation |
| `license_email` | string | No | Email of the licensed user/organisation |
| `maintained` | boolean | No | Whether the license is under maintenance |
| `reassignable` | boolean | No | Whether seats can be reassigned after check-in |
| `notes` | string | No | Notes |
| `min_amt` | number | No | Minimum seat threshold for alerts |

#### `update_license`
Identify by `id` or `name`. Use `new_name` to rename.

#### `delete_license`
The license must have no seats currently assigned. Identify by `id` or `name`.

#### `checkout_license`

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | number | No | Identify by numeric ID |
| `name` | string | No | Identify by name |
| `assigned_to` | number | No | User ID to assign the seat to |
| `asset_id` | number | No | Asset ID to assign the seat to |
| `note` | string | No | Checkout note |

#### `checkin_license`

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `seat_id` | number | **Yes** | ID of the license seat to check in |
| `note` | string | No | Check-in note |

---

### Departments

#### `create_department`

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `name` | string | **Yes** | Department name |
| `location_id` | number | No | Location ID |
| `company_id` | number | No | Company ID |
| `manager_id` | number | No | Manager user ID |
| `phone` | string | No | Department phone number |
| `fax` | string | No | Department fax number |
| `notes` | string | No | Notes |

#### `update_department`
Identify by `id` or `name`. Use `new_name` to rename.

#### `delete_department`
The department must have no users assigned. Identify by `id` or `name`.

---

### Companies

#### `list_companies`
Search with optional `search`, `limit`, `offset`.

#### `show_company`
Look up by `id` or `name`.

#### `create_company`

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `name` | string | **Yes** | Company name |
| `phone` | string | No | Phone number |
| `fax` | string | No | Fax number |
| `email` | string | No | Email address |
| `notes` | string | No | Notes |

#### `update_company`
Identify by `id` or `name`. Use `new_name` to rename.

#### `delete_company`
Identify by `id` or `name`.

---

### Categories

#### `list_categories`

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `search` | string | No | Keyword search |
| `category_type` | string | No | `asset`, `accessory`, `consumable`, `component`, or `license` |
| `limit` | number | No | Results to return (default: 25, max: 500) |
| `offset` | number | No | Results to skip |

#### `show_category`
Look up by `id` or `name`.

#### `create_category`

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `name` | string | **Yes** | Category name |
| `category_type` | string | **Yes** | `asset`, `accessory`, `consumable`, `component`, or `license` |
| `checkin_email` | boolean | No | Send check-in email |
| `require_acceptance` | boolean | No | Require user acceptance on checkout |
| `use_default_eula` | boolean | No | Use the default EULA |
| `notes` | string | No | Notes |

#### `update_category`
Identify by `id` or `name`. Use `new_name` to rename.

#### `delete_category`
The category must have no items assigned. Identify by `id` or `name`.

---

### Manufacturers

#### `list_manufacturers`
Search with optional `search`, `limit`, `offset`.

#### `show_manufacturer`
Look up by `id` or `name`.

#### `create_manufacturer`

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `name` | string | **Yes** | Manufacturer name |
| `url` | string | No | Website URL |
| `support_url` | string | No | Support website URL |
| `support_email` | string | No | Support email |
| `support_phone` | string | No | Support phone |
| `warranty_lookup_url` | string | No | Warranty lookup URL |
| `notes` | string | No | Notes |

#### `update_manufacturer`
Identify by `id` or `name`. Use `new_name` to rename.

#### `delete_manufacturer`
Identify by `id` or `name`.

---

### Suppliers

#### `list_suppliers`
Search with optional `search`, `limit`, `offset`.

#### `show_supplier`
Look up by `id` or `name`.

#### `create_supplier`

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `name` | string | **Yes** | Supplier name |
| `address` | string | No | Address line 1 |
| `address2` | string | No | Address line 2 |
| `city` | string | No | City |
| `state` | string | No | State/province |
| `country` | string | No | Country |
| `zip` | string | No | Postal code |
| `phone` | string | No | Phone number |
| `fax` | string | No | Fax number |
| `email` | string | No | Email address |
| `url` | string | No | Website URL |
| `contact` | string | No | Contact name |
| `notes` | string | No | Notes |

#### `update_supplier`
Identify by `id` or `name`. Use `new_name` to rename.

#### `delete_supplier`
Identify by `id` or `name`.

---

### Status Labels

#### `list_status_labels`

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `search` | string | No | Keyword search |
| `status_type` | string | No | `deployable`, `pending`, `archived`, or `undeployable` |
| `limit` | number | No | Results to return (default: 25, max: 500) |
| `offset` | number | No | Results to skip |

#### `show_status_label`
Look up by `id` or `name`.

#### `create_status_label`

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `name` | string | **Yes** | Status label name |
| `type` | string | **Yes** | `deployable`, `pending`, `archived`, or `undeployable` |
| `color` | string | No | Display colour in `#RRGGBB` format |
| `notes` | string | No | Notes |
| `default_label` | boolean | No | Set as default label |
| `show_in_nav` | boolean | No | Show in navigation |

#### `update_status_label`
Identify by `id` or `name`. Use `new_name` to rename.

#### `delete_status_label`
Identify by `id` or `name`.

---

### Locations

#### `list_locations`

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `search` | string | No | Keyword search |
| `parent_id` | number | No | Filter by parent location ID |
| `limit` | number | No | Results to return (default: 25, max: 500) |
| `offset` | number | No | Results to skip |

#### `show_location`
Look up by `id` or `name`.

#### `create_location`

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `name` | string | **Yes** | Location name |
| `address` | string | No | Street address |
| `address2` | string | No | Address line 2 |
| `city` | string | No | City |
| `state` | string | No | State/province |
| `country` | string | No | Country |
| `zip` | string | No | Postal code |
| `phone` | string | No | Phone number |
| `fax` | string | No | Fax number |
| `currency` | string | No | Currency code |
| `parent_id` | number | No | Parent location ID |
| `manager_id` | number | No | Manager user ID |

#### `update_location`
Identify by `id` or `name`. Use `new_name` to rename.

#### `delete_location`
Identify by `id` or `name`.

---

### Asset Models

#### `list_asset_models`

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `search` | string | No | Keyword search across name, model number |
| `category_id` | number | No | Filter by category |
| `manufacturer_id` | number | No | Filter by manufacturer |
| `limit` | number | No | Results to return (default: 25, max: 500) |
| `offset` | number | No | Results to skip |

#### `show_asset_model`
Look up by `id` or `name`.

#### `create_asset_model`

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `name` | string | **Yes** | Model name |
| `category_id` | number | **Yes** | Category ID |
| `model_number` | string | No | Model number |
| `manufacturer_id` | number | No | Manufacturer ID |
| `depreciation_id` | number | No | Depreciation schedule ID |
| `eol` | number | No | End of life in months (0–240) |
| `min_amt` | number | No | Minimum quantity alert threshold |
| `notes` | string | No | Notes |
| `requestable` | boolean | No | Whether the model can be requested |
| `require_serial` | boolean | No | Whether serial numbers are required |

#### `update_asset_model`
Identify by `id` or `name`. Use `new_name` to rename.

#### `delete_asset_model`
Identify by `id` or `name`.

---

### Depreciations

#### `list_depreciations`
Search with optional `search`, `limit`, `offset`.

#### `show_depreciation`
Look up by `id` or `name`.

#### `create_depreciation`

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `name` | string | **Yes** | Depreciation schedule name |
| `months` | number | **Yes** | Depreciation period in months (1–3600) |

#### `update_depreciation`
Identify by `id` or `name`. Use `new_name` to rename, `months` to change the period.

#### `delete_depreciation`
Identify by `id` or `name`.

---

### Groups

#### `list_groups`
Search with optional `search`, `limit`, `offset`.

#### `show_group`
Look up by `id` or `name`.

#### `create_group`

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `name` | string | **Yes** | Group name (must be unique) |
| `notes` | string | No | Notes |

#### `update_group`
Identify by `id` or `name`. Use `new_name` to rename.

#### `delete_group`
The group must have no users assigned. Identify by `id` or `name`.

---

### Maintenance

The maintenances table is polymorphic (`item_id` + `item_type`). Today only assets carry maintenances but the shape
allows other entity types in the future. The tools below write and read using the native columns, with legacy
`asset_id` / `completion_date` accepted as input aliases for backward compat with older integrations.

#### `list_maintenances`

Search and list maintenances with status, warranty, supplier, and date-range filters.

| Parameter              | Type    | Required | Description                                                                                      |
|------------------------|---------|----------|--------------------------------------------------------------------------------------------------|
| `asset_id`             | number  | No       | Filter by numeric asset ID. Translated to `item_id` + `item_type=Asset` server-side.             |
| `asset_tag`            | string  | No       | Filter by asset tag. Resolved to the matching asset server-side.                                 |
| `supplier_id`          | number  | No       | Filter by the supplier that performed the maintenance                                            |
| `status`               | string  | No       | `open` (`completed_at` is null), `completed` (`completed_at` is set), or `all` (default)         |
| `is_warranty`          | boolean | No       | Filter to warranty vs non-warranty maintenances                                                  |
| `start_date_from`      | string  | No       | Return maintenances whose `start_date` is on or after this date (YYYY-MM-DD)                     |
| `start_date_to`        | string  | No       | Return maintenances whose `start_date` is on or before this date (YYYY-MM-DD)                    |
| `completion_date_from` | string  | No       | Return maintenances whose actual `completed_at` timestamp is on or after this date (YYYY-MM-DD)  |
| `completion_date_to`   | string  | No       | Return maintenances whose actual `completed_at` timestamp is on or before this date (YYYY-MM-DD) |
| `sort`                 | string  | No       | `recent` (default), `oldest`, `start_date`, or `completion_date`                                 |
| `limit`                | number  | No       | Results to return (default: 25, max: 500)                                                        |
| `offset`               | number  | No       | Results to skip                                                                                  |

Response rows include `name`, `item_type`, `item_id`, `asset_tag` (when the item is an Asset), `is_warranty`, `cost`,
`start_date`, `expected_completion_date` (planned), `completed_at` (actual), `supplier`, `supplier_id`,
`maintenance_type_id`, and `notes`.

#### `create_maintenance`

Create a maintenance record attached to an asset. Writes to the polymorphic `item_id` + `item_type` columns directly,
not through the legacy `asset_id` mutator.

Before calling this tool, use `list_maintenance_types` to find the right `maintenance_type_id`. Create a new one via
`create_maintenance_type` only if nothing matching exists, to avoid taxonomy sprawl.

| Parameter                  | Type    | Required | Description                                                                                                                                          |
|----------------------------|---------|----------|------------------------------------------------------------------------------------------------------------------------------------------------------|
| `asset_id`                 | number  | No       | Numeric ID of the asset. One of `asset_id` or `asset_tag` is required.                                                                               |
| `asset_tag`                | string  | No       | Asset tag of the asset. One of `asset_id` or `asset_tag` is required.                                                                                |
| `title`                    | string  | **Yes**  | Maintenance name/title                                                                                                                               |
| `maintenance_type_id`      | number  | **Yes**  | `maintenance_types.id` row identifying the type of work. Look up via `list_maintenance_types`.                                                       |
| `supplier_id`              | number  | No       | Supplier who performed or will perform the work                                                                                                      |
| `is_warranty`              | boolean | No       | Whether this is covered by warranty (defaults to false)                                                                                              |
| `cost`                     | number  | No       | Cost in the install's currency                                                                                                                       |
| `start_date`               | string  | No       | Work start date (YYYY-MM-DD, defaults to today)                                                                                                      |
| `expected_completion_date` | string  | No       | Planned completion date (YYYY-MM-DD). Use when the maintenance is still open.                                                                        |
| `completion_date`          | string  | No       | Alias for `expected_completion_date` kept for backward compat                                                                                        |
| `completed_at`             | string  | No       | Actual completion timestamp (YYYY-MM-DD or YYYY-MM-DD HH:MM:SS). Set this to mark the maintenance done. Also sets `completed_by` to the acting user. |
| `notes`                    | string  | No       | Free-form notes                                                                                                                                      |
| `responsible_party_id`     | number  | No       | Technician user ID responsible for the work                                                                                                          |

Response includes `id`, `name`, `item_type`, `item_id`, and `asset_tag`.

#### `list_maintenance_types`

List the maintenance type taxonomy. Call this before `create_maintenance` to discover the right `maintenance_type_id`
and before `create_maintenance_type` to check that the type you want to add doesn't already exist in some variant.

| Parameter | Type   | Required | Description                                                                                                                     |
|-----------|--------|----------|---------------------------------------------------------------------------------------------------------------------------------|
| `search`  | string | No       | Keyword search across name and tag_color. Use to probe for close matches ("repair", "upgrade", etc) before creating a new type. |
| `limit`   | number | No       | Results to return (default: 25, max: 500)                                                                                       |
| `offset`  | number | No       | Results to skip                                                                                                                 |

Response rows include `id`, `name`, and `tag_color`. Results are ordered alphabetically by name.

#### `create_maintenance_type`

Add a new maintenance type to the taxonomy. Requires `assets.edit` permission. Only use this after
`list_maintenance_types` confirms nothing matching already exists. The underlying unique-name constraint on the table
will reject duplicates with an error if you try to create one anyway.

| Parameter   | Type   | Required | Description                                                                                                         |
|-------------|--------|----------|---------------------------------------------------------------------------------------------------------------------|
| `name`      | string | **Yes**  | Name of the new type (must be unique). Examples: "Repair", "Preventive Maintenance", "Upgrade", "Warranty Service". |
| `tag_color` | string | No       | Optional hex color (e.g. `#ff9900`) used by the UI to color-code rows of this type.                                 |

Response includes `id`, `name`, and `tag_color`. The `id` is what you pass as `maintenance_type_id` on the next
`create_maintenance` call.

---

### Activity Log

#### `get_activity_log`

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `item_type` | string | No | Filter by item type (e.g. `App\Models\Asset`) |
| `item_id` | number | No | Filter by item ID |
| `user_id` | number | No | Filter by user ID |
| `action_type` | string | No | Filter by action (e.g. `checkout`, `checkin`, `update`) |
| `limit` | number | No | Results to return (default: 25, max: 500) |
| `offset` | number | No | Results to skip |
