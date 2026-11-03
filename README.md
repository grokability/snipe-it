![snipe-it-by-grok](https://github.com/grokability/snipe-it/assets/197404/b515673b-c7c8-4d9a-80f5-9fa58829a602)

[![Crowdin](https://d322cqt584bo4o.cloudfront.net/snipe-it/localized.svg)](https://crowdin.com/project/snipe-it) [![Docker Pulls](https://img.shields.io/docker/pulls/snipe/snipe-it.svg)](https://hub.docker.com/r/snipe/snipe-it/)  [![Tests in MySQL](https://github.com/grokability/snipe-it/actions/workflows/tests-mysql.yml/badge.svg)](https://github.com/grokability/snipe-it/actions/workflows/tests-mysql.yml)
[![All Contributors](https://img.shields.io/badge/all_contributors-331-orange.svg?style=flat-square)](#contributing) [![Discord](https://badgen.net/badge/icon/discord?icon=discord&label)](https://discord.gg/yZFtShAcKk)

# Snipe-IT - Open Source Asset Management System

Snipe-IT is a FOSS project for asset management in IT Operations. Knowing who has which laptop, when it was purchased in
order to depreciate it correctly, handling software licenses, etc.

Snipe-IT is actively developed and we [release quite frequently](https://github.com/grokability/snipe-it/releases). ([Check out the live demo here](https://snipeitapp.com/demo/).)

> [!TIP]
> __This is web-based software__. This means there is no executable file (aka no .exe files), and it must be run on a
web server and accessed through a web browser. It runs on any Mac OS X, any flavor of Linux, as well as Windows, and we
have a [Docker image](https://snipe-it.readme.io/docs/docker) available if that's what you're into.

-----

## Table of Contents

* [General Features](#general-features)
* [Included Sync Adapters](#included-sync-adapters)
* [Live Demo](#live-demo)
* [Installation](#installation)
* [User's Manual](#users-manual)
* [Bug Reports & Feature Requests](#bug-reports--feature-requests)
* [Security](#security)
* [Upgrading](#upgrading)
* [Translations!](#translations-)
* [Libraries, Modules & Related Projects](#libraries-modules--related-projects)
* [Join the Community!](#join-the-community)
* [Contributing](#contributing)
* [Announcement List](#announcement-list)

-----

## General Features

<table border="0" width="100%">
<tr>
<td valign="top" width="50%">

🎉 Mobile-friendly for asset updates on the go<br />
🎉 Runs on any Linux, Windows or Mac web server<br />
🎉 Web-based software so it works on any device<br />
🎉 Slack/Teams notification integration for check-in/check-out<br />
🎉 Tons of security features to keep your data safe<br />
🎉 Pre-defined "kits" for faster checkouts<br />
🎉 SAML SSO login<br />
🎉 JAMF integration via Jamf2Snipe or Jira<br />
🎉 Role-based groups with granular permissions<br />
🎉 Easy transferring of items between users<br />
🎉 Per-user light/dark mode<br />
🎉 Optional digital signatures on item acceptance<br />


</td>
<td valign="top" width="50%">

🎉 Includes a robust JSON REST API<br />
🎉 Translated into over 55 languages for easy localization<br />
🎉 Per-user language support for distributed teams<br />
🎉 One-click (or cron) backups<br />
🎉 Active Directory/LDAP login & user sync<br />
🎉 Google Secure LDAP user sync<br />
🎉 SCIM support for automatic user provisioning<br />
🎉 Third-party integrations with RMMs, MDMs, and other cloud services<br />
🎉 CSV import for quickly getting up and running<br />
🎉 Supports multiple companies within one installation<br />
🎉 Native MDM/RMM sync adapters 🆕<br />
🎉 Calendar view 🆕<br />

</td>
</tr>
</table>

Check out the project's [features]([https://snipeitapp.com/features](https://snipeitapp.com/features)) for a more
complete
list!

-----

## Included Sync Adapters

Snipe-IT comes with 25 (and growing!) sync adapters that let you integrate your favorite (or at least begrudgingly
useful) MDM and RMM systems with Snipe-IT so that everything stays updated across all of your tooling.

<table border="0" width="100%">
<colgroup>
<col width="50%" />
<col width="50%" />
</colgroup>
<tr>
<td valign="top">

🔄 Addigy<br />
🔄 Apple Business Manager<br />
🔄 Custom HTTP<br />
🔄 Fleet<br />
🔄 Google Workspace (ChromeOS)<br />
🔄 Iru (Kandji)<br />
🔄 Jamf Platform<br />
🔄 Jamf Pro<br />
🔄 Jamf School<br />
🔄 JumpCloud<br />
🔄 Kaseya VSA 10<br />
🔄 Landscape<br />
🔄 Level.io<br />

</td>
<td valign="top">

🔄 ManageEngine Endpoint Central<br />
🔄 Meraki Systems Manager<br />
🔄 Microsoft Intune<br />
🔄 Mosyle Business<br />
🔄 Mosyle Manager<br />
🔄 NinjaOne<br />
🔄 Omnissa Workspace ONE<br />
🔄 osctrl<br />
🔄 Sophos Central<br />
🔄 UniFi<br />
🔄 UniFi Access<br />
🔄 Zentral<br />

</td>
</tr>
</table>


-----

## Live Demo

Want to try Snipe-IT before installing it? Hop into one of our public sandboxes. No signup required.

- **Stable release**: [demo.snipeitapp.com](https://demo.snipeitapp.com)
- **Develop branch** (previews unreleased features): [develop.snipeitapp.com](https://develop.snipeitapp.com)


- Admin User: Log in as `admin` / `password`.
- Assets Manager User: Log in as `assets` / `password`.
- Test User: Log in as `testuser` / `password`.

> [!IMPORTANT]
> These are **public** demos. Please do not enter real asset, license, or user information while testing. Data resets
several times a day, and email editing, access-level changes, password changes, and outbound email are all disabled to
prevent abuse.


If you hit a maintenance page, give it a few minutes and refresh. That means the demo server is being updated with the
latest code from the develop branch, or the database is being reset.


-----

## Installation

For instructions on installing and configuring Snipe-IT on your server, check out the [installation manual](https://snipe-it.readme.io/docs). (Please see the [requirements documentation](https://snipe-it.readme.io/docs/requirements) for full requirements.)

If you're having trouble with the installation, please check the [Common Issues](https://snipe-it.readme.io/docs/common-issues) and [Getting Help](https://snipe-it.readme.io/docs/getting-help) documentation, and search this repository's open *and* closed issues for help.

-----

## User's Manual
For help using Snipe-IT, check out the [user's manual](https://snipe-it.readme.io/docs/overview).

-----

## Bug Reports & Feature Requests

Feel free to check out the [GitHub Issues for this project](https://github.com/grokability/snipe-it/issues) to open a bug report or see what open issues you can help with. Please search through existing issues (open *and* closed) to see if your question has already been answered before opening a new issue.

> [!IMPORTANT]  
> **PLEASE see the [Getting Help Guidelines](https://snipe-it.readme.io/docs/getting-help) and [Common Issues](https://snipe-it.readme.io/docs/common-issues) before opening a ticket, and be sure to complete all of the questions in the Github Issue template to help us to help you as quickly as possible.**

-----

## Security

> [!IMPORTANT]
> To report a security vulnerability, use
**[GitHub Security Advisories](https://github.com/grokability/snipe-it/security)**
or email [security@snipeitapp.com](mailto:security@snipeitapp.com). Please do not open a regular issue, since
security reports should stay off the
public issue tracker until a fix is released.

We use GitHub Security Advisories (GHSAs) to manage responsible disclosure for the Snipe-IT codebase.

**Before you submit a report, please:**

1. Search the [existing GHSA list](https://github.com/grokability/snipe-it/security/advisories) first. A match may
   already be reported, and if the fix isn't in a tagged release yet, the advisory may not be published.
2. Test against `master` or `develop` to be sure the issue isn't already patched.
3. Leave the package name and ecosystem fields blank. Snipe-IT is not distributed as a package.
4. Specify the version range that is affected.
5. Provide CVSS and CWE references via the GHSA UI.

**Response time:** You can typically expect a response within five business days, and most fixes are released within a
week of the initial disclosure. If you report a vulnerability we've already received from someone else, we will add your
GitHub username to the original GHSA so you get credit for the parallel discovery.

🏆 We do not run a bug bounty program and do not offer monetary rewards, but researchers who report multiple
high-quality vulnerabilities may be added to
our [Security Researchers Hall of Fame](https://snipeitapp.com/thanks#security-researcher-hall-of-fame).

-----

## Upgrading

Please see the [upgrading documentation](https://snipe-it.readme.io/docs/upgrading) for instructions on upgrading Snipe-IT.

------

## Translations!

Please see the [translations documentation](https://snipe-it.readme.io/docs/translations) for information about available languages and how to add translations to Snipe-IT.

-----

## Libraries, Modules & Related Projects

Since the release of the JSON REST API, several third-party developers have been developing modules and libraries to work with Snipe-IT.  

> [!NOTE]
> As these were created by third-parties, Snipe-IT cannot provide support for these projects, and you should contact the
developers directly if you need assistance. Additionally, Snipe-IT makes no guarantees as to the reliability, accuracy
or maintainability of these libraries. Use at your own risk. :)

### Libraries & Modules

- [SnipeScheduler](https://github.com/JSY-Ben/SnipeScheduler) by [@JSY-Ben](https://github.com/JSY-Ben) - An Asset Reservation/Checkout System for Snipe-IT
- [Snipe-IT MCP Server](https://github.com/jameshgordy/snipeit-mcp) by [@jameshgordy](https://github.com/jameshgordy) - A Model Context Protocol (MCP) server for managing Snipe-IT inventory systems
- [SnipeSharp - .NET module in C#](https://github.com/barrycarey/SnipeSharp) by [@barrycarey](https://github.com/barrycarey)
- [SnipeitPS](https://github.com/snazy2000/SnipeitPS) by [@snazy2000](https://github.com/snazy2000) - Powershell API Wrapper for Snipe-it
- [jamf2snipe](https://github.com/grokability/jamf2snipe) - Python script to sync assets between a JAMFPro instance and a Snipe-IT instance
- [jamf-snipe-rename](https://macblog.org/jamf-snipe-rename/) - Python script to rename computers in Jamf from Snipe-IT
- [Snipe-IT plugin for Jira Service Desk](https://marketplace.atlassian.com/apps/1220964/snipe-it-for-jira)
- [Rudder2Snipe](https://github.com/norbertoaquino/rudder2snipe) by [@norbertoaquino](https://github.com/norbertoaquino) - Rudder.io integration for Snipe-IT
- [Python 3 CSV importer](https://github.com/gastamper/snipeit-csvimporter) - allows importing assets into Snipe-IT based on Item Name rather than Asset Tag.
- [Snipe-IT Kubernetes Helm Chart](https://github.com/t3n/helm-charts/tree/master/snipeit) - For more information, [click here](https://hub.helm.sh/charts/t3n/snipeit).
- [Snipe-IT Bulk Edit](https://github.com/bricelabelle/snipe-it-bulkedit) - Google Script files to use Google Sheets as a bulk checkout/checkin/edit tool for Snipe-IT.
- [MosyleSnipeSync](https://github.com/RodneyLeeBrands/MosyleSnipeSync) by [@Karpadiem](https://github.com/Karpadiem) - Python script to synchronize information between Mosyle and Snipe-IT.
- [WWW::SnipeIT](https://github.com/SEDC/perl-www-snipeit) by [@SEDC](https://github.com/SEDC) - perl module for accessing the API
- [UniFi to Snipe-IT](https://www.edtechirl.com/p/snipe-it-and-azure-asset-management) originally by [@karpadiem](https://github.com/karpadiem) - Python script that synchronizes UniFi devices with Snipe-IT.
- [Kandji2Snipe](https://github.com/grokability/kandji2snipe) by [@briangoldstein](https://github.com/briangoldstein) - Python script that synchronizes Kandji with Snipe-IT.
- [snipeit-warranty-sync](https://github.com/checkwarranty/snipeit-warranty-sync)
  by [@checkwarranty](https://github.com/checkwarranty) - Python script to synchronize hardware warranty expiration
  dates and factory specs (Dell, Lenovo, HP, Apple) with Snipe-IT.
- [SnipeAgent](https://github.com/ReticentRobot/SnipeAgent) by [@ReticentRobot](https://github.com/ReticentRobot) - Windows agent for Snipe-IT.
- [Gate Pass Generator](https://github.com/cha7uraAE/snipe-it-gate-pass-system) by [@cha7uraAE](https://github.com/cha7uraAE) - A Streamlit application for generating gate passes based on hardware data from a Snipe-IT API.
- [IT-Tools](https://github.com/chrisnox/Snipeit-it-tools) by [@chrisnox](https://github.com/chrisnox) - Browser
  bookmarklets for PDF handover/return
  protocols, digital signatures, label printing (Zebra ZD410), AirWatch MDM sync and Lansweeper CSV import.

We also have a handful of [Google Apps scripts](https://github.com/grokability/google-apps-scripts-for-snipe-it) to help with various tasks.

-----

## Mobile Apps

Our [official mobile app](https://github.com/grokability/snipe-it-mobile) is currently in beta, and other developers
have also created native mobile apps for Snipe-IT using our API.

**For updates on the official mobile app**, join us in
the [#official-mobile-app channel](https://discord.com/channels/849770616521359401/1430282366995267584) in the
**[Snipe-IT Discord](https://discord.gg/yZFtShAcKk)** server. To download the beta, grab it
on [Testflight (iOS)](https://testflight.apple.com/join/3B3YcWph) or
the [Google Play store for Android](https://play.google.com/store/apps/details?id=com.grokability.snipeitmobile).

Third-party apps built on the Snipe-IT API:

- [SnipeMate](https://snipemate.app/) (iOS, Google Play, Huawei AppGallery) by Mars Technology
- [Snipe-Scan](https://apps.apple.com/do/app/snipe-scan/id6744179400?uo=2) (iOS) by Nicolas Maton
- [Snipe-IT Assets Management](https://play.google.com/store/apps/details?id=com.diegogarciadev.assetsmanager.snipeit&hl=en&pli=1)
  (Google Play) by DiegoGarciaDEV\*
- [AssetX for Snipe-IT](https://apps.apple.com/my/app/assetx-for-snipe-it/id6741996196?uo=2) (iOS) by Rishi Gupta
- [SnipeScanner](https://apps.apple.com/au/app/snipescanner/id6746058955) (iOS) by Jimmy Fletcher
- [SnipeMobile](https://github.com/Callandt/SnipeMobile) (iOS, beta) by Avery Callandt

\* Requires a file to be placed on the Snipe-IT server. Not compatible with hosted versions.

-----

## Join the Community!

- **[Join our Discord](https://discord.gg/yZFtShAcKk)!** It’s full of great people. We even wrote about it [here](https://grokstar.dev/culture/2024/06/the-unlikely-rise-of-discord-as-a-support-channel/)!
- **Follow us on Bluesky** at [@snipeitapp.com](https://bsky.app/profile/snipeitapp.com)
- **Follow us on Mastodon** at [hachyderm.io/@grokability](https://hachyderm.io/@grokability)
- **Follow our blog** at [Grokstar.Dev](https://grokstar.dev)
- **Subscribe here** on Github for notifications about new releases. (We recommend selecting "Releases" only for most users - this repo can get noisy.)

-----

## Contributing

**Please refrain from submitting issues or pull requests generated by fully-automated tools. Maintainers reserve the right, at their sole discretion, to close such submissions and to block any account responsible for them.** Please see our [AI Contribution Policy](https://snipe-it.readme.io/docs/contributing-overview#ai-usage-policy) for more information.

Contributions should follow from a human-to-human discussion in the form of an issue for the best chances of being
merged into the core project. (Sometimes we might already be working on that feature, sometimes we've decided against it
for a reason, and sometimes we just need to discuss the best way to implement it.)

Please see the complete documentation on [contributing and developing for Snipe-IT](https://snipe-it.readme.io/docs/contributing-overview).

This project is released with a [Contributor Code of Conduct](CODE_OF_CONDUCT.md). By participating in this project you agree to abide by its terms.

The ERD is available [online here](https://drawsql.app/templates/snipe-it).

Be sure to check out all of the [amazing people](CONTRIBUTORS.md) that have contributed to Snipe-IT over the years!

------

## Announcement List

To be notified of important news (such as new releases, security advisories, etc), [sign up for our list](http://eepurl.com/XyZKz). We'll never sell or give away your info, and we'll only email you when it's important. 

We also usually make smaller announcements on our social accounts, our Discord, and our blog, so be sure to subscribe to those if you're looking for more granular announcements.
