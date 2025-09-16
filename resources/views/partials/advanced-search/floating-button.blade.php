<div id="floatingButtonContainer" class="fab-fixed-wrapper">
    <button id="filterButton" type="button" class="fab" id="searchButton" title="Search" aria-label="Search">
        <i class="fa-solid fa-magnifying-glass"></i>
    </button>

    <button type="button" class="fab" id="menuToggleButton" title="Menu" aria-label="Toggle menu" aria-haspopup="true"
        aria-expanded="false" aria-controls="fabMenu">
        <i class="fa-solid fa-bars"></i>
    </button>

    <br />
    <div class="menu" id="fabMenu" role="menu" aria-labelledby="menuToggleButton">
        <a href="#" id="storeFilterButton" class="menuButton" role="menuitem" tabindex="1">{{ trans('button.save_predefined_filter_as') }}</a>
        <a href="#" id="updateFilterButton" class="menuButton" role="menuitem" tabindex="2">{{ trans('button.update_predefined_filter') }}</a>
        <a href="#" id="deleteFilterButton" class="menuButton" role="menuitem" tabindex="3">{{ trans('button.delete_predefined_filter') }}</a>
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

    const menuButtonItems = document.querySelectorAll(".menuButton");
    menuButtonItems.forEach((item) => {
        item.addEventListener("click", closeMenu);
    });

    window.addEventListener("resize", alignFloatingButtons);
    window.addEventListener("orientationchange", alignFloatingButtons);
    window.addEventListener("load", alignFloatingButtons);
</script>


<style>
    /* Fix the buttons to bottom of screen */
    .fab-fixed-wrapper {
        position: fixed;
        bottom: 20px;
        left: 10.5vw;
        transform: translateX(-50%);
        display: flex;
        gap: 20px;
        z-index: 1000;
        pointer-events: auto;
    }

    /* Limit width to match container if needed */
    #advancedSearchFilters {
        position: relative;
    }

    /* Optional: align wrapper to container width */
    .fab-fixed-wrapper {
        padding: 0 15px;
    }

    /* Center inner buttons within max-width */
    .fab-fixed-wrapper {
        justify-content: center;
    }

    /* FAB style */
    .fab {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background-color: var(--button-primary);
        color: white;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        transition: background 0.3s;
    }

    .fab:hover {
        background-color: var(--button-hover);
    }

    #menuToggle {
        display: none;
    }

    /* Label to toggle menu */
    .menu-button-label {
        position: absolute;
        bottom: 20px;
        left: 50%;
        transform: translateX(38px);
        width: 56px;
        height: 56px;
        cursor: pointer;
        z-index: 101;
    }

    .menu {
        position: absolute;
        bottom: 6vh;
        left: 50%;
        transform: translateX(38px);
        background: white;
        border: 1px solid #ccc;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        display: none;
        flex-direction: column;
        min-width: 120px;
        z-index: -99;
    }

    .menu a {
        padding: 10px 10px;
        text-decoration: none;
        color: #333;
        border-bottom: 1px solid #eee;
    }

    .menu a:last-child {
        border-bottom: none;
    }

    .menu a:hover {
        background-color: #f8f8f8;
    }

    #menuToggle:checked~.floating-span .menu {
        display: flex;
    }
</style>