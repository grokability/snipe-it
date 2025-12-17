<div id="floatingButtonContainer" class="floating-buttons-fab-fixed-wrapper">

    <div class="floating-buttons-inner">
        <button id="filterButton" type="button" class="floating-buttons-fab" title="Search" aria-label="Search">
            <i class="fa-solid fa-magnifying-glass"></i>
        </button>

        <button type="button" class="floating-buttons-fab" id="menuToggleButton" title="Menu" aria-label="Toggle menu" aria-haspopup="true"
            aria-expanded="false" aria-controls="fabMenu">
            <i class="fa-solid fa-bars"></i>
        </button>

        <div class="floating-buttons-menu" id="fabMenu" role="menu" aria-labelledby="menuToggleButton" aria-hidden="true">
            <a href="#" id="storeFilterButton" class="floating-buttons-menuButton" role="menuitem" tabindex="-1">{{ trans('button.save_predefined_filter_as') }}</a>
            <a href="#" id="updateFilterButton" class="floating-buttons-menuButton floating-buttons-disabled" role="menuitem" tabindex="-1">{{ trans('button.update_predefined_filter') }}</a>
            <a href="#" id="deleteFilterButton" class="floating-buttons-menuButton floating-buttons-disabled" role="menuitem" tabindex="-1">{{ trans('button.delete_predefined_filter') }}</a>
        </div>
    </div>
</div>
