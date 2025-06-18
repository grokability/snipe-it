![snipe-it-by-grok](https://github.com/grokability/snipe-it/assets/197404/b515673b-c7c8-4d9a-80f5-9fa58829a602)

[![Crowdin](https://d322cqt584bo4o.cloudfront.net/snipe-it/localized.svg)](https://crowdin.com/project/snipe-it) [![Docker Pulls](https://img.shields.io/docker/pulls/snipe/snipe-it.svg)](https://hub.docker.com/r/snipe/snipe-it/) [![Codacy Badge](https://app.codacy.com/project/badge/Grade/804dd1beb14a41f38810ab77d64fc4fc)](https://app.codacy.com/gh/grokability/snipe-it/dashboard?utm_source=gh&utm_medium=referral&utm_content=&utm_campaign=Badge_grade) [![Tests](https://github.com/grokability/snipe-it/actions/workflows/tests.yml/badge.svg)](https://github.com/grokability/snipe-it/actions/workflows/tests.yml)
[![All Contributors](https://img.shields.io/badge/all_contributors-331-orange.svg?style=flat-square)](#contributing) [![Discord](https://badgen.net/badge/icon/discord?icon=discord&label)](https://discord.gg/yZFtShAcKk)

## Snipe-IT - Open Source Asset Management System

This is a FOSS project for asset management in IT Operations. Knowing who has which laptop, when it was purchased in order to depreciate it correctly, handling software licenses, etc.

It is built on [Laravel 11](http://laravel.com).

Snipe-IT is actively developed and we [release quite frequently](https://github.com/grokability/snipe-it/releases). ([Check out the live demo here](https://snipeitapp.com/demo/).)

> [!TIP]
> __This is web-based software__. This means there is no executable file (aka no .exe files), and it must be run on a web server and accessed through a web browser. It runs on any Mac OSX, any flavor of Linux, as well as Windows, and we have a [Docker image](https://snipe-it.readme.io/docs/docker) available if that's what you're into.

-----

### Table of Contents
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

### Installation

For instructions on installing and configuring Snipe-IT on your server, check out the [installation manual](https://snipe-it.readme.io/docs). (Please see the [requirements documentation](https://snipe-it.readme.io/docs/requirements) for full requirements.)

If you're having trouble with the installation, please check the [Common Issues](https://snipe-it.readme.io/docs/common-issues) and [Getting Help](https://snipe-it.readme.io/docs/getting-help) documentation, and search this repository's open *and* closed issues for help.

-----
### User's Manual
For help using Snipe-IT, check out the [user's manual](https://snipe-it.readme.io/docs/overview).

-----
### Bug Reports & Feature Requests

Feel free to check out the [GitHub Issues for this project](https://github.com/grokability/snipe-it/issues) to open a bug report or see what open issues you can help with. Please search through existing issues (open *and* closed) to see if your question has already been answered before opening a new issue.

> [!IMPORTANT]  
> **PLEASE see the [Getting Help Guidelines](https://snipe-it.readme.io/docs/getting-help) and [Common Issues](https://snipe-it.readme.io/docs/common-issues) before opening a ticket, and be sure to complete all of the questions in the Github Issue template to help us to help you as quickly as possible.**

-----

### Security

> [!IMPORTANT]
> **To report a security vulnerability, please email security@snipeitapp.com instead of using the issue tracker.**
-----


### Upgrading

Please see the [upgrading documentation](https://snipe-it.readme.io/docs/upgrading) for instructions on upgrading Snipe-IT.

------
### Translations!

Please see the [translations documentation](https://snipe-it.readme.io/docs/translations) for information about available languages and how to add translations to Snipe-IT.

-----

### Libraries, Modules & Related Projects

Since the release of the JSON REST API, several third-party developers have been developing modules and libraries to work with Snipe-IT.  

> [!NOTE]  
> As these were created by third-parties, Snipe-IT cannot provide support for these project, and you should contact the developers directly if you need assistance. Additionally, Snipe-IT makes no guarantees as to the reliability, accuracy or maintainability of these libraries. Use at your own risk. :)

#### Libraries & Modules

- [SnipeSharp - .NET module in C#](https://github.com/barrycarey/SnipeSharp) by [@barrycarey](https://github.com/barrycarey)
- [SnipeitPS](https://github.com/snazy2000/SnipeitPS) by [@snazy2000](https://github.com/snazy2000) - Powershell API Wrapper for Snipe-it
- [jamf2snipe](https://github.com/grokability/jamf2snipe) - Python script to sync assets between a JAMFPro instance and a Snipe-IT instance
- [jamf-snipe-rename](https://macblog.org/jamf-snipe-rename/) - Python script to rename computers in Jamf from Snipe-IT
- [Snipe-IT plugin for Jira Service Desk](https://marketplace.atlassian.com/apps/1220964/snipe-it-for-jira)
- [Python 3 CSV importer](https://github.com/gastamper/snipeit-csvimporter) - allows importing assets into Snipe-IT based on Item Name rather than Asset Tag.
- [Snipe-IT Kubernetes Helm Chart](https://github.com/t3n/helm-charts/tree/master/snipeit) - For more information, [click here](https://hub.helm.sh/charts/t3n/snipeit).
- [Snipe-IT Bulk Edit](https://github.com/bricelabelle/snipe-it-bulkedit) - Google Script files to use Google Sheets as a bulk checkout/checkin/edit tool for Snipe-IT.
- [MosyleSnipeSync](https://github.com/RodneyLeeBrands/MosyleSnipeSync) by [@Karpadiem](https://github.com/Karpadiem) - Python script to synchronize information between Mosyle and Snipe-IT.
- [WWW::SnipeIT](https://github.com/SEDC/perl-www-snipeit) by [@SEDC](https://github.com/SEDC) - perl module for accessing the API
- [UniFi to Snipe-IT](https://www.edtechirl.com/p/snipe-it-and-azure-asset-management) originally by [@karpadiem](https://github.com/karpadiem) - Python script that synchronizes UniFi devices with Snipe-IT.
- [Kandji2Snipe](https://github.com/grokability/kandji2snipe) by [@briangoldstein](https://github.com/briangoldstein) - Python script that synchronizes Kandji with Snipe-IT.
- [SnipeAgent](https://github.com/ReticentRobot/SnipeAgent) by [@ReticentRobot](https://github.com/ReticentRobot) - Windows agent for Snipe-IT.
- [Gate Pass Generator](https://github.com/cha7uraAE/snipe-it-gate-pass-system) by [@cha7uraAE](https://github.com/cha7uraAE) - A Streamlit application for generating gate passes based on hardware data from a Snipe-IT API.
- [InQRy (archived)](https://github.com/Microsoft/InQRy) by [@Microsoft](https://github.com/Microsoft)
- [Marksman (archived)](https://github.com/Scope-IT/marksman) - A Windows agent for Snipe-IT
- [Python Module (archived)](https://github.com/jbloomer/SnipeIT-PythonAPI) by [@jbloomer](https://github.com/jbloomer)

We also have a handful of [Google Apps scripts](https://github.com/grokability/google-apps-scripts-for-snipe-it) to help with various tasks.

#### Mobile Apps

We're currently working on our own mobile app, but in the meantime, check out these third-party apps that work with Snipe-IT:

- [SnipeMate](https://snipemate.app/) (iOS, Google Play, Huawei AppGallery) by Mars Technology
- [Snipe-Scan](https://apps.apple.com/do/app/snipe-scan/id6744179400?uo=2) (iOS) by Nicolas Maton
- [Snipe-IT Assets Management](https://play.google.com/store/apps/details?id=com.diegogarciadev.assetsmanager.snipeit&hl=en&pli=1) (Google Play) by DiegoGarciaDEV
- [AssetX](https://apps.apple.com/my/app/assetx-for-snipe-it/id6741996196?uo=2) (iOS) for Snipe-IT by Rishi Gupta

-----

### Join the Community!

- **[Join our Discord](https://discord.gg/yZFtShAcKk)!** It’s full of great people. We even wrote about it [here](https://grokstar.dev/culture/2024/06/the-unlikely-rise-of-discord-as-a-support-channel/)!
- **Follow us on Bluesky** at [@snipeitapp.com](https://bsky.app/profile/snipeitapp.com)
- **Follow us on Mastodon** at [hachyderm.io/@grokability](https://hachyderm.io/@grokability)
- **Follow our blog** at [Grokstar.Dev](https://grokstar.dev)
- **Subscribe here** on Github for notifications about new releases. (We recommend selecting "Releases" only for most users - this repo can get noisy.)

-----

### Contributing

**Please refrain from submitting issues or pull requests generated by fully-automated tools. Maintainers reserve the right, at their sole discretion, to close such submissions and to block any account responsible for them.** 

Contributions should follow from a human-to-human discussion in the form of an issue for the best chances of being merged into the core project. (Sometimes we might already be working on that feature, sometimes we've decided against )

Please see the complete documentation on [contributing and developing for Snipe-IT](https://snipe-it.readme.io/docs/contributing-overview).

This project is released with a [Contributor Code of Conduct](CODE_OF_CONDUCT.md). By participating in this project you agree to abide by its terms.

The ERD is available [online here](https://drawsql.app/templates/snipe-it).

Be sure to check out all of the [amazing people](CONTRIBUTORS.md) that have contributed to Snipe-IT over the years!

-----

### Star History

[![Star History Chart](https://api.star-history.com/svg?repos=grokability/snipe-it&type=Date)](https://www.star-history.com/#grokability/snipe-it&Date)

------
### Announcement List

To be notified of important news (such as new releases, security advisories, etc), [sign up for our list](http://eepurl.com/XyZKz). We'll never sell or give away your info, and we'll only email you when it's important. 

We also usually make smaller announcements on our social accounts, our Discord, and our blog, so be sure to subscribe to those if you're looking for more granular announcements.
