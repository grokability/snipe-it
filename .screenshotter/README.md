# Screenshotter

A Playwright-driven walkthrough of the Snipe-IT UI that produces PNG screenshots for docs, marketing, or reference use. The script logs in, navigates through canonical pages and interactions, and writes screenshots to an out-of-repo directory (`.screenshotter/screenshots/` by default, gitignored). What you do with the resulting PNGs is up to you.

## Requirements

- Node 18+ (uses `node:util.parseArgs`).
- Playwright and Chromium. Both are already installed as dev dependencies for this repo, so `npm install` is enough to have them available.
- A running Snipe-IT install to point at. Herd or `php artisan serve` both work. Default target is `https://snipe-it.test`.
- Credentials for a superuser on that install. Default is `admin` / `password`, which is what the demo seeder creates.

## Data caution (read this)

The script captures whatever is live in the connected database, in full. **DO NOT** run it against a production install, a staging environment that mirrors production, or any database that contains real customer data, real user PII, uploaded avatars or documents, license keys, IP addresses, employee numbers, or anything else you would not personally publish in a public forum.

Once a screenshot exists on your disk it is one drag-and-drop away from GitHub, Discord, Reddit, a support ticket, a hosted docs site, a Slack message, a bug report attachment, or any other place where you might casually share a screenshot to explain something. Screenshots pulled from a real install have been the source of real data leaks in real projects. Do not become the next one. Stick to demo-seeded installs unless you have personally reviewed the data on that install.

## Usage

Point it at any Snipe-IT install and it will screenshot whatever is there. In practice you want a freshly seeded demo install (see the caution above) so the shots are reproducible and safe to publish.

```bash
# Recommended: reseed first so the shots reflect canonical demo data
php artisan migrate:fresh --seed
npm run screenshotter

# Also fine, if you know what is in your local DB and it is safe to capture
npm run screenshotter
```

At startup the script prints its config so you can see what mode you're in, and at the end it prints how long the run took:

```
Base URL:  https://snipe-it.test
Login as:  admin
Output:    .screenshotter/screenshots
Viewport:  1840x900
Framing:   local (generic-light)
Submit:    on (edit forms are posted after shot)
Tabs:      off (view pages shoot base only)
Color:     light

→ logging in
→ assets (as admin)
  ✓ assets/admin-assets-index-...png (framed)
  ...
Done. 166 screenshots written to .screenshotter/screenshots in 4m 12.3s.
```

Full runs wipe the walkthrough shots at the start of every run so stale images never mix with fresh ones. Only `.screenshotter/README.md`, `.screenshotter/src/screenshotter.mjs`, and the `.screenshotter/screenshots/adhoc/` subdirectory (see ad-hoc mode below) are preserved.

## All environment overrides

```bash
BASE_URL=https://staging.example.com    # default: https://snipe-it.test
USERNAME=snipe                          # default: admin
PASSWORD=secret                         # default: password
OUT=/tmp/snipe-shots                    # default: .screenshotter/screenshots
HEADLESS=false                          # default: true; false to watch it run
VIEWPORT_WIDTH=1920 VIEWPORT_HEIGHT=1080 # default: 1840x900
FRAME=false                             # default: true
SUBMIT_FORMS=false                      # default: true
TABS=true                               # default: false
ALL_ROUTES=false                        # default: true
COLOR_SCHEME=dark                       # default: light
TABLE_PAGE_SIZE=25                      # default: 10; bootstrap-table rows per shot
```

`HEADLESS=false` runs the browser visibly so you can watch the walkthrough, which is useful when adding new blocks and debugging selectors. `TABLE_PAGE_SIZE` shrinks index tables so screenshots don't get needlessly long from data that adds no docs value.

## Side effects on the database

The walkthrough posts each edit form after screenshotting it (to capture the post-save UI, whether that is a success callout or a validation-error state) so a full run writes back to the connected database. In practice this means:

- Every first-class object gets one no-op update per viewer, which appends an `action_logs` row per submission.
- Any observer/notification/webhook wired to an update event fires as if a real edit happened. On a demo install this is usually fine, but if the install has outbound webhooks pointed at a real endpoint (Slack, an internal service, etc.) those fire too.
- No data is intentionally changed (the forms are submitted with the values already on the page), but "unchanged" is not the same as "no side effects."

Before submitting, the script forces the form's `redirect_option=index` hidden field so the post-save destination is always the section's index page. This gives a stable "success callout on the index" shot regardless of what Snipe-IT's default `redirect_option` handling would have picked based on session state.

For a demo-seeded local install this is expected and fine. For anything else, do not run the full walkthrough (see the data caution above), or disable the submit step:

```bash
# Skip the write-back and the `-edit-submitted` shots
SUBMIT_FORMS=false npm run screenshotter
```

With `SUBMIT_FORMS=false` the walkthrough is read-only: no form posts, no `action_logs` entries, no observer/webhook fires. Trade-off is you lose the post-save UI captures.

## Dev-tool overlays are blocked at the network level

Debugbar, Telescope, and Clockwork all get their asset requests aborted via Playwright network interception. Their JS never loads, so their overlays cannot render, so no debug panel ever appears in a shot. This is stronger than CSS hiding, which was the previous approach and broke on Snipe-IT error pages where debugbar rendered visible JSON collector panels through selectors we couldn't reach.

If you add another dev tool that injects a page-level overlay, add its asset path to the `context.route(...)` block near the top of the script.

## Narrowing to specific sections

Use `--section <name>` to regenerate just one or more sections instead of the whole walkthrough. Section names match the directory under `screenshots/`, and the section filter also selects which resource-managers run (managers whose section isn't in the filter are skipped).

```bash
# Just the assets section (admin + assetmgr)
npm run screenshotter -- --section assets

# Comma-separated for multiple
npm run screenshotter -- --section settings,reports,dashboard

# Standalone sections work too
npm run screenshotter -- --section dashboard
```

Sections available: `assets`, `licenses`, `accessories`, `consumables`, `components`, `users`, `models`, `categories`, `manufacturers`, `suppliers`, `locations`, `departments`, `kits`, `companies`, `statuslabels`, `depreciations`, `custom-fields`, `fieldsets`, `maintenance-types`, `dashboard`, `settings`, `reports`.

When `--section` is set, other sections' shots from prior runs are preserved (not wiped), and the `all-routes` sweep is skipped since it's not tied to any section.

## Walking view-page tabs

Off by default. Set `TABS=true` to include a shot of every Bootstrap tab pane on view pages (asset view alone has ~10 tabs: Licenses, Components, Maintenances, Audits, Notes, Files, and so on). Shot names look like `{section}/{user}-{section}-view-tab-{slug}`.

```bash
# Include all tabs
TABS=true npm run screenshotter

# Just assets, with tabs
TABS=true npm run screenshotter -- --section assets
```

Skipped silently on pages without any tabs. The already-active tab is skipped too since the base view shot already captured its content.

## Light and dark mode

`COLOR_SCHEME=light` (default) or `COLOR_SCHEME=dark`. Uses Playwright's `colorScheme` context option which sets `prefers-color-scheme` at the browser level. Snipe-IT users whose theme preference is "system" render in the requested scheme automatically, without needing to toggle anything in the UI.

```bash
COLOR_SCHEME=dark npm run screenshotter
COLOR_SCHEME=dark npm run screenshotter -- --section assets
```

If a user's theme preference is set to something specific ("always dark" or "always light"), the app will honor that regardless of `prefers-color-scheme`, so this flag has no effect for those accounts.

## Browser-chrome framing

Every generated screenshot is wrapped in a styled browser chrome by default (rounded corners, gray titlebar with three traffic-light dots, soft ambient drop shadow radiating on all four sides). Set `FRAME=false` to skip the frame post-processing and get raw viewport shots instead.

```bash
# Framed (default)
npm run screenshotter

# Raw shots, no frame
FRAME=false npm run screenshotter
```

Framing is done entirely locally via an inline HTML template plus a Playwright screenshot of the composed result. No external services are called, no image content leaves your machine.

The frame's address bar shows the URL path of the shot (e.g. `/hardware/1/edit`) as a rounded pill centered in the chrome. Only the path is rendered, not the full URL. This keeps things clean regardless of what your local testing host is (`snipe-it.test`, an ngrok tunnel, etc.) and avoids leaking your local hostname into published images.

## Ad-hoc single-shot mode

Skip the full walkthrough and capture just one URL as a specific user. Useful for regenerating one stale image without re-running the whole sweep, or grabbing an off-catalog page for a one-off.

```bash
# Just a URL, using the default USERNAME (admin)
node .screenshotter/src/screenshotter.mjs --one /hardware

# As a specific role
node .screenshotter/src/screenshotter.mjs --one /hardware/create --as assetmgr

# With a custom output name
node .screenshotter/src/screenshotter.mjs --one /licenses/5 --as licensemgr --name license-detail

# Shoot a specific tab on a view page
node .screenshotter/src/screenshotter.mjs --one /hardware/1 --tab licenses
node .screenshotter/src/screenshotter.mjs --one /hardware/1 --tab "components"

# Dark mode, no framing
COLOR_SCHEME=dark FRAME=false node .screenshotter/src/screenshotter.mjs --one /hardware --as admin
```

Ad-hoc shots land in `.screenshotter/screenshots/adhoc/{username}-{name}-{timestamp}.png`, or with `-tab-{slug}` in the filename when `--tab` is used. The `adhoc/` directory is deliberately preserved across full walkthrough runs so historical one-off images stick around, and every ad-hoc shot carries a timestamp so repeated captures of the same URL never overwrite each other.

Arguments:

- `--one <path>` (required) URL path to shoot, with or without a leading slash.
- `--as <username>` (default: `USERNAME` env, which defaults to `admin`) user to log in as. Any seeded user works: `admin`, `snipe`, `assetmgr`, `licensemgr`, `accessorymgr`, `consumablemgr`, `componentmgr`, `usermgr`, etc.
- `--name <slug>` (default: URL path with `/` replaced by `__`) filename slug. The timestamp is appended automatically.
- `--tab <label>` case-insensitive substring match against the visible Bootstrap tab labels on the target page. `--tab licenses` matches "Licenses" or "Licenses (5)" alike. If nothing matches, the run aborts with a list of available tabs so you can pick a valid one.

## What a full run produces

Every generated PNG follows the naming convention `{section}/{username}-{section}-{page}-{timestamp}.png` so alphabetical sort groups shots by section, then by role, then by run. The section appears in both the directory name and the filename so a single PNG shared out of context (dropped into a Discord thread, a PR comment, a support ticket) is still self-identifying.

Example section directory contents after a run as `admin` plus the resource managers:

```
.screenshotter/screenshots/assets/
├── admin-assets-index-2026-07-21-141230.png
├── admin-assets-view-2026-07-21-141230.png
├── admin-assets-edit-2026-07-21-141230.png
├── admin-assets-edit-submitted-2026-07-21-141230.png
├── admin-assets-checkout-2026-07-21-141230.png
├── admin-assets-create-2026-07-21-141230.png
├── admin-assets-create-status-dropdown-2026-07-21-141230.png
├── admin-assets-bulk-checkout-2026-07-21-141230.png
├── admin-assets-bulk-checkin-2026-07-21-141230.png
├── assetmgr-assets-index-2026-07-21-141230.png
├── assetmgr-assets-view-2026-07-21-141230.png
├── assetmgr-assets-edit-2026-07-21-141230.png
├── assetmgr-assets-edit-submitted-2026-07-21-141230.png
├── assetmgr-assets-checkout-2026-07-21-141230.png
└── assetmgr-assets-create-2026-07-21-141230.png
```

Coverage per section:

- **Index, view, edit, edit-submitted** for every first-class object.
- **Info-panel toggle** on view pages that have one: the base `-view` shot captures the default (expanded) state; an extra `-view-info-collapsed` (or `-view-info-expanded` if the initial state happened to be collapsed) captures the other. Docs can then show both compact and expanded layouts.
- **Checkout** for the checkoutable ones (assets, licenses, accessories, consumables, components, kits).
- **Create form** as an extra where useful (assets, users, licenses).
- **Bulk-checkout and bulk-checkin** under the assets section (`/hardware/bulkcheckout`, `/hardware/bulkcheckin`).
- **Interaction shots** (only assets today: the status dropdown open on the create form).

Sections in the default walkthrough: `assets`, `licenses`, `accessories`, `consumables`, `components`, `users`, `models`, `categories`, `manufacturers`, `suppliers`, `locations`, `departments`, `kits`, `companies`, `statuslabels`, `depreciations`, `custom-fields`, `fieldsets`, `maintenance-types`, `dashboard`, `settings`, `reports`.

Also shot as separate walkthroughs from the perspective of scoped resource-managers (each seeded with permissions for exactly one resource): `assetmgr`, `licensemgr`, `accessorymgr`, `consumablemgr`, `componentmgr`, `usermgr`. These land in the same section directories as the admin shots for side-by-side comparison.

Finally, a superuser sweep of every parameter-free GET route in the app, filed under `all-routes/`, for visual gut-check purposes. Set `ALL_ROUTES=false` to skip that pass.

## How to add a new screenshot

Each block in the script is intentionally explicit. Adding a new page or interaction means adding a small block that navigates, waits, and calls the `shot(name)` helper.

```js
await page.goto(`${BASE_URL}/consumables`);
await waitForTable();
await shot(`consumables/${USERNAME}-consumables-index`);
```

For interaction shots (dropdown open, modal open, mid-flow state), click the trigger, wait for the target element to appear, then screenshot. The script uses `page.waitForLoadState('networkidle')` inside the `shot()` helper to defuse AdminLTE's async rendering, and a `waitForTable()` helper waits for bootstrap-table's loading overlay to clear before shooting a table page.

If your new block covers a first-class object (list plus detail plus edit page), add an entry to the `firstClassObjects` config array near the top of the walkthrough section and the loop will produce the three shots for free. Add `hasCheckout: true` if the entity is checkoutable, `hasView: false` if it has no detail page.

## Shooting the same page as different users

The script has an `asUser(username, fn)` block helper that clears cookies, logs in as the named user, runs the callback, then any shots inside the block are captured under that session.

```js
await asUser('viewer', async () => {
    await page.goto(`${BASE_URL}/hardware`);
    await waitForTable();
    await shot('assets/viewer-assets-index');
});
```

Users referenced in these blocks must exist in the seeded database. The demo seeders ship the six resource-manager users (assetmgr, licensemgr, accessorymgr, consumablemgr, componentmgr, usermgr) plus the standard admin/snipe accounts. Add more via `UserFactory` states if you need finer-grained roles for docs comparison shots.

## Workflow expectation

When a PR adds or meaningfully changes a user-visible screen, modal, form, dropdown, or interaction, add or update the corresponding block in `.screenshotter/src/screenshotter.mjs` in the same PR. If a downstream doc references a specific screenshot filename and the script's block for it is removed, the doc visibly breaks the next time it is regenerated, which is the intended feedback loop.

## Implementation notes

The script uses the `.mjs` extension rather than `.js` so it always runs as an ES module regardless of what the root `package.json` says. The alternative was to add `"type": "module"` to `package.json`, which would flip every other `.js` file in the repo to ESM at the same time and is a much larger change than this script warrants.

Source and output are separated (`.screenshotter/src/` vs `.screenshotter/screenshots/`) so the wipe-before-run logic can never accidentally delete the script itself. An earlier version had them in the same directory with a filename-based skip list, which self-deleted the running script the moment someone renamed the output directory.
