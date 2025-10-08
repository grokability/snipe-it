<div id="floatingButtonContainer" class="floatingButtons-fab-fixed-wrapper">
    @push('css')
        <link rel="stylesheet" href="{{ mix('css/dist/floating-buttons.min.css') }}">
    @endpush
    <button id="filterButton" type="button" class="floatingButtons-fab" id="searchButton" title="Search" aria-label="Search">
        <i class="fa-solid fa-magnifying-glass"></i>
    </button>

    <button type="button" class="floatingButtons-fab" id="menuToggleButton" title="Menu" aria-label="Toggle menu" aria-haspopup="true"
        aria-expanded="false" aria-controls="fabMenu">
        <i class="fa-solid fa-bars"></i>
    </button>

    <br />
    <div class="floatingButtons-menu" id="fabMenu" role="menu" aria-labelledby="menuToggleButton">
        <a href="#" id="storeFilterButton" class="floatingButtons-menuButton" role="menuitem" tabindex="1">{{ trans('button.save_predefined_filter_as') }}</a>
        <a href="#" id="updateFilterButton" class="floatingButtons-menuButton floatingButtons-disabled" role="menuitem" tabindex="2">{{ trans('button.update_predefined_filter') }}</a>
        <a href="#" id="deleteFilterButton" class="floatingButtons-menuButton floatingButtons-disabled" role="menuitem" tabindex="3">{{ trans('button.delete_predefined_filter') }}</a>
    </div>
</div>


<script>
    const advancedSearchPanel = document.getElementById("advancedSearchPanel");
    const floatingButtonContainer = document.getElementById("floatingButtonContainer");
    const menuToggleButton = document.getElementById("menuToggleButton");
    const fabMenu = document.getElementById("fabMenu");
    const menuItems = fabMenu.querySelectorAll("[role='menuitem']");

    function alignFloatingButtons() {
        if (!advancedSearchPanel || !floatingButtonContainer) return;

        const panelRect = advancedSearchPanel.getBoundingClientRect();
        const scrollLeft = window.pageXOffset || document.documentElement.scrollLeft;
        const centerX = panelRect.left + (panelRect.width / 2) + scrollLeft;

        floatingButtonContainer.style.left = `${centerX}px`;
        floatingButtonContainer.style.transform = "translateX(-50%)";
    }

    function showFloatingButtons() {
        floatingButtonContainer.style.visibility = "visible";
    }

    function hideFloatingButtons() {
        floatingButtonContainer.style.visibility = "hidden";
    }

    let menuOpen = false;

    function toggleMenu() {
        menuOpen = !menuOpen;
        fabMenu.style.display = menuOpen ? "flex" : "none";
        menuToggleButton.setAttribute("aria-expanded", String(menuOpen));

        if (menuOpen) {
            // Focus the first menu item
            menuItems[0].setAttribute("tabindex", "0");
            menuItems[0].focus();
        } else {
            // Remove focusability from all items
            menuItems.forEach(item => item.setAttribute("tabindex", "-1"));
        }
    }

    function closeMenu() {
        if (menuOpen) {
            menuOpen = false;
            fabMenu.style.display = "none";
            menuToggleButton.setAttribute("aria-expanded", "false");
            menuItems.forEach(item => item.setAttribute("tabindex", "-1"));
        }
    }

    function floatingMenuEnableEditDeleteButtons() {
        document.getElementById("updateFilterButton").classList.remove('floatingButtons-disabled');
        document.getElementById("deleteFilterButton").classList.remove('floatingButtons-disabled');
    }

    function floatingMenuDisableEditDeleteButtons() {
        document.getElementById("updateFilterButton").classList.add('floatingButtons-disabled');
        document.getElementById("deleteFilterButton").classList.add('floatingButtons-disabled');
    }

    // Toggle menu on button click
    menuToggleButton.addEventListener("click", toggleMenu);

    // Toggle menu with keyboard (Enter / Space)
    menuToggleButton.addEventListener("keydown", (e) => {
        if (e.key === "Enter" || e.key === " ") {
            e.preventDefault();
            toggleMenu();
        }
    });

    // Close menu on Escape or click outside
    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape") {
            closeMenu();
            menuToggleButton.focus();
        }
    });

    document.addEventListener("click", (e) => {
        if (!fabMenu.contains(e.target) && !menuToggleButton.contains(e.target)) {
            closeMenu();
        }
    });

    // Close menu if user clicks outside
    document.addEventListener("click", (e) => {
        if (!floatingButtonContainer.contains(e.target)) {
            fabMenu.style.display = "none";
        }
    });

    const menuButtonItems = document.querySelectorAll(".floatingButtons-menuButton");
    menuButtonItems.forEach((item) => {
        item.addEventListener("click", closeMenu);
    });

    window.addEventListener("resize", alignFloatingButtons);
    window.addEventListener("orientationchange", alignFloatingButtons);
    window.addEventListener("load", alignFloatingButtons);
</script>
