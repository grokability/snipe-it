# Snipe-IT MCP Server

This directory contains the Model Context Protocol (MCP) server implementation for Snipe-IT. It exposes the Snipe-IT asset management database to AI assistants through a standard interface, allowing them to look up assets, manage users, process checkouts and check-ins, and run common IT workflows — all in plain language.

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

Claude Desktop will initiate the OAuth flow on first connection. Once authorised, you can use tools and prompts directly in conversation:

> "Check out asset LAPTOP-0042 to jsmith."

> "What's assigned to sarah.chen right now?"

### MCP Inspector

The [MCP Inspector](https://laravel.com/docs/12.x/mcp#mcp-inspector) is useful for exploring and testing tools before
integrating with a client:

1. Run `php artisan mcp:inspector SnipeITMcpServer` in your terminal
2. Open the provided URL in your browser

### Cursor / VS Code / Other MCP Clients

Any client that supports the MCP Streamable HTTP transport and OAuth can connect using the same URL. Refer to your client's documentation for how to add a remote MCP server.

---

## Authentication

The server uses **OAuth 2.0 with dynamic client registration**. Clients that support OAuth will handle this automatically. On first connection:

1. The client discovers the OAuth server via `/.well-known/oauth-authorization-server`
2. It registers itself dynamically via `/oauth/register`
3. The user is redirected to Snipe-IT's login page to authorise access
4. The client receives a bearer token and uses it for all subsequent requests

The authenticated user's Snipe-IT permissions apply — a user without `delete assets` permission cannot call `delete_asset`, for example.

---

## Prompts

Prompts are pre-built conversation starters for common multi-step workflows. In clients that support them (such as Claude Desktop), prompts appear in a slash-command menu or prompt picker. Select a prompt, fill in any arguments, and the AI will walk through the workflow using the available tools automatically.

---

### `onboard_employee`

**Guide through creating a new employee account and assigning appropriate equipment and licenses.**

Creates the user account, finds available assets suitable for their role, checks equipment out to them, and assigns any relevant license seats.

| Argument | Required | Description |
|----------|----------|-------------|
| `first_name` | Yes | First name of the new employee |
| `last_name` | No | Last name of the new employee |
| `department` | No | Department the employee will join |
| `location` | No | Primary office location |
| `title` | No | Job title |

**Example usage in Claude:**
> Use the `onboard_employee` prompt → first_name: "Marcus", last_name: "Webb", department: "Engineering", location: "Austin HQ"

---

### `offboard_employee`

**Check in all equipment and licenses from a departing employee and deactivate their account.**

Looks up everything assigned to the user, checks in all assets and accessories, revokes license seats, and deactivates the account.

| Argument | Required | Description |
|----------|----------|-------------|
| `username` | Yes | Username of the departing employee |

**Example usage in Claude:**
> Use the `offboard_employee` prompt → username: "marcus.webb"

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

## Tools Reference

Tools are individual actions the AI can call directly. They can also be combined freely in conversation without using a prompt — just describe what you want in plain language.

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
Search and list assets with optional filters.

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `search` | string | No | Keyword search across tag, serial, name, model |
| `status_type` | string | No | `RTD`, `Deployed`, `Archived`, `Pending`, or `Undeployable` |
| `company_id` | number | No | Filter by company |
| `location_id` | number | No | Filter by location |
| `category_id` | number | No | Filter by category |
| `model_id` | number | No | Filter by model |
| `manufacturer_id` | number | No | Filter by manufacturer |
| `limit` | number | No | Results to return (default: 25, max: 500) |
| `offset` | number | No | Results to skip for pagination |

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
Search and list users.

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `search` | string | No | Keyword search across name, username, email, employee number |
| `company_id` | number | No | Filter by company |
| `department_id` | number | No | Filter by department |
| `location_id` | number | No | Filter by location |
| `activated` | boolean | No | Filter by account activated status |
| `limit` | number | No | Results to return (default: 25, max: 500) |
| `offset` | number | No | Results to skip for pagination |

#### `show_user`
Look up a single user by numeric ID, username, or email address.

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | number | No | Numeric user ID |
| `username` | string | No | Username |
| `email` | string | No | Email address |

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

---

### Accessories

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
| `requestable` | boolean | No | User-requestable flag |
| `notes` | string | No | Notes |

#### `update_accessory`
Identify by `id` or `name`. Use `new_name` to rename.

#### `delete_accessory`
The accessory must have no units currently checked out. Identify by `id` or `name`.

#### `checkout_accessory`

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
| `notes` | string | No | Notes |

#### `update_component`
Identify by `id` or `name`. Use `new_name` to rename.

#### `delete_component`
The component must have no units checked out to assets. Identify by `id` or `name`.

#### `checkout_component`

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

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `search` | string | No | Keyword search |
| `company_id` | number | No | Filter by company |
| `category_id` | number | No | Filter by category |
| `manufacturer_id` | number | No | Filter by manufacturer |
| `location_id` | number | No | Filter by location |
| `limit` | number | No | Results to return (default: 25, max: 500) |
| `offset` | number | No | Results to skip |

#### `show_consumable`
Look up by `id` or `name`.

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
| `requestable` | boolean | No | User-requestable flag |
| `notes` | string | No | Notes |

#### `update_consumable`
Identify by `id` or `name`. Use `new_name` to rename.

#### `delete_consumable`
The consumable must have no units currently checked out. Identify by `id` or `name`.

#### `checkout_consumable`

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | number | No | Identify by numeric ID |
| `name` | string | No | Identify by name |
| `assigned_to` | number | **Yes** | User ID to check out to |
| `note` | string | No | Checkout note |

---

### Licenses

#### `list_licenses`

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `search` | string | No | Keyword search across name, serial, notes, order number |
| `company_id` | number | No | Filter by company |
| `category_id` | number | No | Filter by category |
| `manufacturer_id` | number | No | Filter by manufacturer |
| `supplier_id` | number | No | Filter by supplier |
| `limit` | number | No | Results to return (default: 25, max: 500) |
| `offset` | number | No | Results to skip |

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

#### `list_maintenances`

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `asset_id` | number | No | Filter by asset ID |
| `limit` | number | No | Results to return (default: 25, max: 500) |
| `offset` | number | No | Results to skip |

#### `create_maintenance`

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `asset_id` | number | **Yes** | Asset ID the maintenance is for |
| `title` | string | **Yes** | Maintenance title |
| `asset_maintenance_type` | string | No | Type (e.g. `maintenance`, `repair`, `upgrade`) |
| `supplier_id` | number | No | Supplier ID |
| `is_warranty` | boolean | No | Whether this is a warranty repair |
| `cost` | number | No | Cost of the maintenance |
| `start_date` | string | No | Start date (YYYY-MM-DD, defaults to today) |
| `completion_date` | string | No | Completion date (YYYY-MM-DD) |
| `notes` | string | No | Notes |
| `user_id` | number | No | Technician user ID |

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
