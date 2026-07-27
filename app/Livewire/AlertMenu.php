<?php

namespace App\Livewire;

use App\Helpers\Helper;
use Illuminate\View\View;
use Livewire\Attributes\Lazy;
use Livewire\Component;

/**
 * Top-nav alert bell. Wraps the old blade/alert-menu.blade.php partial so
 * the low-inventory + deprecation queries no longer block first-paint.
 *
 * `#[Lazy]` makes the component render a lightweight placeholder on the
 * initial page load, then Livewire fires a second XHR to hydrate the
 * real body — so `Helper::checkLowInventory()` and
 * `Helper::deprecationCheck()` (both cached at the helper layer with
 * observer-driven invalidation) never sit on the critical render path.
 *
 * The setting-gate lives on the tag in layouts/default.blade.php:
 * `@if ($snipeSettings->show_alerts_in_menu == '1') <livewire:alert-menu />`
 * so nothing about this component runs when the operator turned the
 * bell off.
 */
#[Lazy]
class AlertMenu extends Component
{
    public function placeholder(): string
    {
        // Reserve the same visual footprint as the loaded bell so the
        // top-nav doesn't shift when the real component swaps in.
        return <<<'HTML'
            <li class="dropdown tasks-menu" aria-busy="true">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fa fa-bell" aria-hidden="true"></i>
                </a>
            </li>
        HTML;
    }

    public function render(): View
    {
        return view('livewire.alert-menu', [
            'alert_items' => Helper::checkLowInventory(),
            'deprecations' => Helper::deprecationCheck(),
        ]);
    }
}
