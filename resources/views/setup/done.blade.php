@extends('layouts/setup')
{{-- Page title --}}
@section('title')
{{ trans('general.create_admin_user') }}
@parent
@stop

{{-- Page content --}}
@section('content')

    <style>
        .well-warning {
            color: #8a6d3b;
            background-color: #fcf8e3;
            border-color: #faebcc;
        }
    </style>
    <!-- Notifications -->
    <div class="col-md-12">

        <p>
            If you're already familiar with Snipe-IT, you can get started right away by <strong><a href="{{ config('app.url') }}">heading right to your dashboard</a></strong>, or if it's your first time using Snipe-IT, you can check out some of the useful resources below:
        </p>
        <div class="well well-sm">
            <div class="row">
                <div class="col-md-6">
                    <ul>
                        <li><i class="fa-solid fa-book fa-fw"></i> <a href="https://snipe-it.readme.io/docs/overview#/" target="_blank">Overview <x-icon type="external-link" /></a></li>
                        <li><i class="fa-solid fa-book fa-fw"></i> <a href="https://snipe-it.readme.io/docs/getting-started#/" target="_blank">Getting Started <x-icon type="external-link" /></a></li>
                        <li><i class="fa-solid fa-book fa-fw"></i> <a href="https://snipe-it.readme.io/reference/api-overview#/" target="_blank">API Documentation <x-icon type="external-link" /></a></li>
                        <li><i class="fa-solid fa-book fa-fw"></i> <a href="https://snipe-it.readme.io/docs/importing-users#/" target="_blank">Importing Users <x-icon type="external-link" /></a></li>
                        <li><i class="fa-solid fa-book fa-fw"></i> <a href="https://snipe-it.readme.io/docs/importing-assets#/" target="_blank">Importing Assets <x-icon type="external-link" /></a></li>
                    </ul>
                </div>

                <div class="col-md-6">
                    <ul>
                        <li><i class="fa-solid fa-book fa-fw"></i> <a href="https://snipe-it.readme.io/reference/api-overview#/" target="_blank">API Documentation <x-icon type="external-link" /></a></li>
                        <li><i class="fa-solid fa-book fa-fw"></i> <a href="https://snipe-it.readme.io/docs/saml#/" target="_blank">SAML Authentication<x-icon type="external-link" /></a></li>
                        <li><i class="fa-solid fa-book fa-fw"></i> <a href="https://snipe-it.readme.io/docs/scim#/" target="_blank">SCIM <x-icon type="external-link" /></a></li>
                        <li><i class="fa-solid fa-book fa-fw"></i> <a href="https://snipe-it.readme.io/docs/ldap-sync-login#/" target="_blank">LDAP Sync &amp; Login <x-icon type="external-link" /></a></li>
                        <li><i class="fa-solid fa-book fa-fw"></i> <a href="https://snipe-it.readme.io/docs/webhook-integration#/" target="_blank">Webhooks <x-icon type="external-link" /></a></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="well well-sm well-warning">

            <p>
                <x-icon type="tip" /> <strong>Important Note Syncing Users via SCIM or LDAP</strong>
            </p>

            <p>
                If you plan on using SCIM or LDAP syncing to keep your user lists up to date with your directory services,
                make sure the username format for any users imported via CSV matches your directory service username format to avoid duplicating users in Snipe-IT.
            </p>
        </div>

        <p>
            Don't forget to join our communities! You can find us on:
        </p>

            <ul>
                <li><i class="fa-brands fa-github fa-fw"></i> <a href="https://github.com/grokability/snipe-it" target="_blank">Github <x-icon type="external-link" /></a></li>
                <li><i class="fa-brands fa-discord fa-fw"></i> <a href="https://discord.gg/yZFtShAcKk" target="_blank">Discord <x-icon type="external-link" /></a></li>
                <li><i class="fa-brands fa-bluesky fa-fw"></i> <a href="https://bsky.app/profile/snipeitapp.com" target="_blank">Bluesky <x-icon type="external-link" /></a></li>
                <li><i class="fa-brands fa-mastodon fa-fw"></i> <a href="https://hachyderm.io/@grokability" target="_blank">Mastodon <x-icon type="external-link" /></a></li>
                <li><i class="fa-solid fa-square-rss fa-fw"></i> Our blog at <a href="https://grokstar.dev" target="_blank">Grokstar.Dev <x-icon type="external-link" /></a></li>
            </ul>

            <p>
                Subscribe on Github for notifications about new releases. (We recommend selecting "Releases Only" for most users - the repo can get noisy.)
            </p>

    </div>

@stop

@section('button')
    <a class="btn btn-primary" href="{{ config('app.url') }}">{{ trans('admin/settings/general.create_admin_redirect') }}
        <i class="fa-solid fa-angles-right"></i>
    </a>
    @parent
@stop

<script>
    var duration = 2000;
    var animationEnd = Date.now() + duration;
    var defaults = { startVelocity: 30, spread: 360, ticks: 60, zIndex: 0 };

    function randomInRange(min, max) {
        return Math.random() * (max - min) + min;
    }

    var interval = setInterval(function() {
        var timeLeft = animationEnd - Date.now();

        if (timeLeft <= 0) {
            return clearInterval(interval);
        }

        var particleCount = 50 * (timeLeft / duration);
        // since particles fall down, start a bit higher than random
        confetti({ ...defaults, particleCount, origin: { x: randomInRange(0.1, 0.3), y: Math.random() - 0.2 } });
        confetti({ ...defaults, particleCount, origin: { x: randomInRange(0.7, 0.9), y: Math.random() - 0.2 } });
    }, 250);

</script>

