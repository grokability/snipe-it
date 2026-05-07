<?php

namespace App\Helpers;

class IconHelper
{
    public static function icon($type)
    {
        switch ($type) {
            case 'apple':
                return 'fa-brands fa-apple';
            case 'google':
                return 'fa-brands fa-google';
            case 'checkout':
                return 'fa-solid fa-rotate-left';
            case 'checkin':
                return 'fa-solid fa-rotate-right';
            case 'edit':
            case 'update':
                return 'fas fa-pencil-alt';
            case 'clone':
                return 'far fa-clone';
            case 'upload':
                return 'fa-solid fa-file-circle-plus';
            case 'delete':
            case 'upload deleted':
                return 'fas fa-trash';
            case 'create':
                return 'fa-solid fa-plus';
            case 'audit':
                return 'fa-solid fa-clipboard-check';
            case '2fa reset':
                return 'fa-solid fa-mobile-screen';
            case 'new-user':
                return 'fa-solid fa-user-plus';
            case 'merged-user':
                return 'fa-solid fa-people-arrows';
            case 'delete-user':
                return 'fa-solid fa-user-minus';
            case 'update-user':
                return 'fa-solid fa-user-pen';
            case 'user':
                return 'fa-solid fa-user';
            case 'users':
                return 'fas fa-users';
            case 'supplier':
                return 'fa-solid fa-store';
            case 'restore':
                return 'fa-solid fa-trash-arrow-up';
            case 'external-link':
                return 'fa fa-external-link';
            case 'link':
                return 'fa fa-link';
            case 'email':
                return 'fa-regular fa-envelope';
            case 'phone':
                return 'fa-solid fa-phone';
            case 'fax':
                return 'fa-solid fa-fax';
            case 'mobile':
                return 'fas fa-mobile-screen-button';
            case 'long-arrow-right':
                return 'fas fa-long-arrow-alt-right';
            case 'download':
                return 'fas fa-download';
            case 'checkmark':
                return 'fas fa-check';
            case 'x':
                return 'fas fa-times';
            case 'logout':
                return 'fa fa-sign-out';
            case 'admin-settings':
                return 'fas fa-cogs';
            case 'settings':
                return 'fas fa-cog';
            case 'angle-left':
                return 'fas fa-angle-left';
            case 'angle-right':
                return 'fas fa-angle-right';
            case 'warning':
                return 'fas fa-exclamation-triangle';
            case 'kits':
                return 'fas fa-object-group';
            case 'assets':
            case 'asset':
                return 'fas fa-barcode';
            case 'accessories':
            case 'accessory':
                return 'far fa-keyboard';
            case 'components':
            case 'component':
                return 'far fa-hdd';
            case 'consumables':
            case 'consumable':
                return 'fas fa-tint';
            case 'licenses':
            case 'license':
                return 'far fa-save';
            case 'requests':
            case 'requestable':
            case 'request':
            case 'requested':
                return 'fa-solid fa-bell-concierge';
            case 'reports':
                return 'fas fa-chart-bar';
            case 'heart':
                return 'fas fa-heart';
            case 'circle':
                return 'fa-regular fa-circle';
            case 'circle-solid':
                return 'fa-solid fa-circle';
            case 'due':
                return 'fas fa-history';
            case 'import':
                return 'fas fa-cloud-upload-alt';
            case 'search':
                return 'fas fa-search';
            case 'alerts':
                return 'far fa-flag';
            case 'password':
                return 'fa-solid fa-key';
            case 'api-key':
                return 'fas fa-user-secret';
            case 'nav-toggle':
                return 'fas fa-bars';
            case 'dashboard':
                return 'fas fa-tachometer-alt';
            case 'info-circle':
                return 'fas fa-info-circle';
            case 'caret-right':
                return 'fa fa-caret-right';
            case 'caret-up':
                return 'fa fa-caret-up';
            case 'caret-down':
                return 'fa fa-caret-down';
            case 'arrow-circle-right':
                return 'fa fa-arrow-circle-right';
            case 'minus':
                return 'fas fa-minus';
            case 'spinner':
                return 'fas fa-spinner fa-spin';
            case 'copy-clipboard':
                return 'fa-regular fa-clipboard';
            case 'paperclip':
                return 'fas fa-paperclip';
            case 'files':
                return 'fa-solid fa-file-contract';
            case 'contact-card':
                return 'fa-regular fa-id-card';
            case 'eula':
            case 'eulas':
                return 'fa-regular fa-handshake';
            case 'star':
            case 'vip':
                return 'fa-solid fa-star';
            case 'remote':
                return 'fa-solid fa-house-laptop';
            case 'more-info':
            case 'support':
                return 'far fa-life-ring';
            case 'plus':
                return 'fas fa-plus';
            case 'history':
                return 'fa-solid fa-timeline';
            case 'more-files':
                return 'fa-solid fa-laptop-file';
            case 'maintenances':
                return 'fa-solid fa-screwdriver-wrench';
            case 'seats':
                return 'far fa-list-alt';
            case 'globe-us':
                return 'fas fa-globe-americas';
            case 'locked':
                return 'fas fa-lock';
            case 'unlocked':
                return 'fas fa-lock';
            case 'locations':
                return 'fas fa-map-marker-alt';
            case 'location':
                return 'fas fa-map-marker-alt';
            case 'superadmin':
            case 'admin':
                return 'fas fa-crown';
            case 'print':
                return 'fa-solid fa-print';
            case 'checkin-and-delete':
                return 'fa-solid fa-user-xmark';
            case 'branding':
                return 'fas fa-copyright';
            case 'general-settings':
                return 'fa-solid fa-list-check';
            case 'groups':
                return 'fa-solid fa-user-group';
            case 'bell':
                return 'fa-solid fa-bell';
            case 'hashtag':
                return 'fa-solid fa-hashtag';
            case 'asset-tags':
                return 'fas fa-list-ol';
            case 'labels':
                return 'fas fa-tags';
            case 'ldap':
                return 'fas fa-sitemap';
            case 'google':
                return 'fa-brands fa-google';
            case 'saml':
                return 'fas fa-sign-in-alt';
            case 'backups':
                return 'fas fa-file-archive';
            case 'logins':
                return 'fas fa-crosshairs';
            case 'oauth':
                return 'fas fa-user-secret';
            case 'employee_num':
                return 'fa-regular fa-id-card';
            case 'department':
                return 'fa-solid fa-building-user';
            case 'home':
                return 'fa-solid fa-house';
            case 'note':
            case 'notes':
                return 'fas fa-sticky-note';
            case 'tip':
                return 'fa-solid fa-lightbulb';
            case 'highlight':
                return 'fa-solid fa-highlighter';
            case 'manager':
                return 'fa-solid fa-user-tie';
            case 'company':
                return 'fa-regular fa-building';
            case 'parent':
                return 'fa-solid fa-building-flag';
            case 'number':
                return 'fa-solid fa-hashtag';
            case 'depreciation':
                return 'fa-solid fa-arrows-down-to-line';
            case 'calendar':
                return 'fas fa-calendar';
            case 'depreciation-calendar':
            case 'expiration':
            case 'terminates':
                return 'fa-regular fa-calendar-xmark';
            case 'deleted-date':
            case 'end_date':
                return 'fa-solid fa-calendar-xmark';
            case 'expected_checkin':
            case 'start_date':
                return 'fa-solid fa-calendar-check';
            case 'eol':
                return 'fa-regular fa-calendar-days';
            case 'manufacturer':
                return 'fa-solid fa-industry';
            case 'fieldset':
                return 'fa-regular fa-rectangle-list';
            case 'category':
                return 'fa-solid fa-icons';
            case 'cost':
                return 'fa-solid fa-money-bills';
            case 'available':
                return 'fa-solid fa-box';
            case 'checkedout':
                return 'fa-solid fa-box-open';
            case 'purchase_order':
                return 'fa-solid fa-file-invoice-dollar';
            case 'order':
                return 'fa-solid fa-file-invoice';
            case 'checkout-all':
                return 'fa-solid fa-arrows-down-to-people';
            case 'checkin-all':
                return 'fa-solid fa-arrows-turn-right';
            case 'square-right':
                return 'fa-regular fa-square-caret-right';
            case 'square-left':
                return 'fa-regular fa-square-caret-left';
            case 'square':
                return 'fa-solid fa-square';
            case 'models':
            case 'model':
                return 'fa-solid fa-boxes-stacked';
            case 'min-qty':
                return 'fa-solid fa-chart-pie';

        }
    }
}
