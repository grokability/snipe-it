<?php

namespace App\Helpers;

class IconHelper
{

    public static function icon($type) {
        switch ($type) {
            case 'checkout':
                return 'fa-solid fa-rotate-left';
            case 'checkin':
                return 'fa-solid fa-rotate-right';
            case 'edit':
                return 'fas fa-pencil-alt';
            case 'clone':
                return 'far fa-clone';
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
            case 'restore':
                return 'fa-solid fa-trash-arrow-up';
            case 'external-link':
                return 'fa fa-external-link';
            case 'email':
                return 'fa-regular fa-envelope';
            case 'phone':
                return 'fa-solid fa-phone';
            case 'mobile':
                return 'fas fa-mobile-screen-button';
            case 'long-arrow-right':
                return 'fas fa-long-arrow-alt-right';
            case 'download':
                return 'fas fa-download';
            case 'checkmark':
                return 'fas fa-check icon-white';
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
            case 'requestable':
                return 'fas fa-laptop';
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
                return 'fa-solid fa-user-secret';
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
                return 'fa-regular fa-file';
            case 'more-info':
                return 'far fa-life-ring';
            case 'calendar':
                return 'fas fa-calendar';
            case 'plus':
                return 'fas fa-plus';
            case 'history':
                return 'fas fa-history';
            case 'more-files':
                return 'fa-solid fa-laptop-file';
            case 'maintenances':
                return 'fas fa-wrench';
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
            case 'employee_num' :
                return 'fa-regular fa-id-card';
            case 'department' :
                return 'fa-solid fa-building-user';
            case 'home' :
                return 'fa-solid fa-house';
            case 'note':
            case 'notes':
                return 'fas fa-sticky-note';
            case 'tip':
                return 'fa-solid fa-lightbulb';
            case 'highlight':
                return 'fa-solid fa-highlighter';
        }
    }
}
