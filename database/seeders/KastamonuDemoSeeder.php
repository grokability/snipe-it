<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class KastamonuDemoSeeder extends Seeder
{
    public function run(): void
    {
        $settings = Setting::first();

        if (!$settings) {
            $this->command->error('No settings record found. Run the default seeder first.');
            return;
        }

        // ══════════════════════════════════════════
        //  1. Locations & Departments
        // ══════════════════════════════════════════
        $this->command->info('');
        $this->command->info('=== [1/4] Locations & Departments ===');
        $this->call(KastamonuLocationsSeeder::class);

        // ══════════════════════════════════════════
        //  2. Custom Fields & Fieldset
        // ══════════════════════════════════════════
        $this->command->info('');
        $this->command->info('=== [2/4] Custom Fields ===');
        $this->call(KastamonuCustomFieldsSeeder::class);

        // ══════════════════════════════════════════
        //  3. Assets (Categories, Manufacturers, Models, 80 Assets)
        // ══════════════════════════════════════════
        $this->command->info('');
        $this->command->info('=== [3/4] Assets ===');
        $this->call(KastamonuAssetsSeeder::class);

        // ══════════════════════════════════════════
        //  4. Branding & Settings
        // ══════════════════════════════════════════
        $this->command->info('');
        $this->command->info('=== [4/4] Branding & Settings ===');

        $settings->site_name = 'Kastamonu Entegre - Ekipman Yönetim Sistemi';
        $settings->brand = 2; // logo only
        $settings->logo = 'kastamonu-logo.png';
        $settings->header_color = '#ffffff';
        $settings->skin = 'green';
        $settings->locale = 'tr-TR';
        $settings->default_currency = 'TRY';
        $settings->date_display_format = 'd.m.Y';
        $settings->time_display_format = 'H:i';

        $settings->custom_css = <<<'CSS'
/* ============================================
   Kastamonu Entegre A.Ş. — Brand Overrides
   Primary: #2d6a2d | Secondary: #1a3d1a
   Logo has white background — navbar matches white
   ============================================ */

/* --- Navbar: white background to match logo --- */
.main-header .navbar,
.navbar-custom {
    background-color: #ffffff !important;
    border-bottom: 3px solid #2d6a2d !important;
}

/* --- Logo area: also white --- */
.main-header .logo {
    background-color: #ffffff !important;
    color: #1a3d1a !important;
}

.main-header .logo:hover {
    background-color: #f5f5f5 !important;
}

/* --- Navbar text/icons: dark green --- */
.main-header .navbar .nav > li > a,
.main-header .navbar .sidebar-toggle,
.main-header .navbar .navbar-nav > li > a {
    color: #1a3d1a !important;
}

.main-header .navbar .nav > li > a:hover,
.main-header .navbar .sidebar-toggle:hover,
.main-header .navbar .navbar-nav > li > a:hover {
    color: #2d6a2d !important;
    background-color: #f0f7f0 !important;
}

/* --- Search bar styling --- */
.main-header .navbar .form-control {
    border-color: #2d6a2d !important;
}

.main-header .navbar .btn-default {
    background-color: #2d6a2d !important;
    border-color: #2d6a2d !important;
    color: #ffffff !important;
}

/* --- Sidebar --- */
.skin-green .sidebar-menu > li.active > a,
.skin-green .sidebar-menu > li:hover > a {
    background-color: #1a3d1a !important;
    border-left-color: #2d6a2d !important;
}

.skin-green .sidebar-menu > li > .treeview-menu {
    background-color: #1a3d1a !important;
}

/* --- Buttons --- */
.btn-primary {
    background-color: #2d6a2d !important;
    border-color: #1a3d1a !important;
}

.btn-primary:hover,
.btn-primary:focus,
.btn-primary:active {
    background-color: #1a3d1a !important;
    border-color: #1a3d1a !important;
}

/* --- Links --- */
a {
    color: #2d6a2d;
}

a:hover,
a:focus {
    color: #1a3d1a;
}

/* Keep sidebar links white */
.sidebar a {
    color: #ffffff;
}

/* --- Form focus states --- */
.form-control:focus {
    border-color: #2d6a2d !important;
    box-shadow: 0 0 0 0.2rem rgba(45, 106, 45, 0.25) !important;
}

/* --- Pagination --- */
.pagination > .active > a,
.pagination > .active > a:hover,
.pagination > .active > span,
.pagination > .active > span:hover {
    background-color: #2d6a2d !important;
    border-color: #1a3d1a !important;
}

/* --- Progress bars & badges --- */
.progress-bar,
.label-default,
.badge-default {
    background-color: #2d6a2d !important;
}

/* --- Login page --- */
.login-box-body .btn-primary,
.login-page .btn-primary {
    background-color: #2d6a2d !important;
    border-color: #1a3d1a !important;
}
CSS;

        $settings->save();
        Setting::$_cache = null;

        $this->command->info("  Site name:    {$settings->site_name}");
        $this->command->info("  Logo:         {$settings->logo}");
        $this->command->info("  Header color: {$settings->header_color}");
        $this->command->info("  Skin:         {$settings->skin}");
        $this->command->info("  Locale:       {$settings->locale}");
        $this->command->info("  Currency:     {$settings->default_currency}");
        $this->command->info("  Date format:  {$settings->date_display_format}");
        $this->command->info("  Brand mode:   {$settings->brand} (logo only)");
        $this->command->info("  Custom CSS:   " . strlen($settings->custom_css) . " bytes");

        $this->command->info('');
        $this->command->info('=== Kastamonu Entegre demo setup complete ===');
    }
}
