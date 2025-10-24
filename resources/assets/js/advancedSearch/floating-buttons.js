// resources/js/modules/floating-buttons.js

export default class FloatingButtons {
    constructor() {
        this.advancedSearchPanel = document.getElementById("advancedSearchPanel");
        this.floatingButtonContainer = document.getElementById("floatingButtonContainer");
        this.menuToggleButton = document.getElementById("menuToggleButton");
        this.fabMenu = document.getElementById("fabMenu");
        this.menuItems = this.fabMenu?.querySelectorAll("[role='menuitem']");
        this.menuOpen = false;

        this.init();
    }

    init() {
        this.bindEvents();
        this.align();
        this.updatePositionMode();
    }

    bindEvents() {
        queueMicrotask(() => {
            // Event Listeners
            this.menuToggleButton?.addEventListener("click", () => this.toggleMenu());

            this.menuToggleButton?.addEventListener("keydown", (e) => {
                if (e.key === "Enter" || e.key === " ") {
                    e.preventDefault();
                    this.toggleMenu();
                }
            });

            document.addEventListener("keydown", (e) => {
                if (e.key === "Escape") {
                    this.closeMenu();
                    this.menuToggleButton.focus();
                }
            });

            document.addEventListener("click", (e) => {
                if (!this.fabMenu.contains(e.target) && !this.menuToggleButton.contains(e.target)) {
                    this.closeMenu();
                }
            });

            const menuButtonItems = document.querySelectorAll(".floatingButtons-menuButton");
            menuButtonItems.forEach((item) => {
                item.addEventListener("click", () => this.closeMenu());
            });

            window.addEventListener("resize", () => {
                this.align();
                this.updatePositionMode();
            });
            window.addEventListener("orientationchange", () => {
                this.align();
                this.updatePositionMode();
            });
            window.addEventListener("load", () => {
                this.align();
                this.updatePositionMode();
            });
            window.addEventListener("scroll", () => {
                this.updatePositionMode();
            });
        });
    }

    align() {
        if (!this.advancedSearchPanel || !this.floatingButtonContainer) return;

        queueMicrotask(() => {
            const panelRect = this.advancedSearchPanel.getBoundingClientRect();
            const scrollLeft = window.pageXOffset || document.documentElement.scrollLeft;
            const centerX = panelRect.left + (panelRect.width / 2) + scrollLeft;

            this.floatingButtonContainer.style.left = `${centerX}px`;
            this.floatingButtonContainer.style.transform = "translateX(-50%)";
        });
    }

    updatePositionMode() {
        if (!this.advancedSearchPanel || !this.floatingButtonContainer) return;

        queueMicrotask(() => {
            const panelRect = this.advancedSearchPanel.getBoundingClientRect();

            // Check if panel is actually visible and has height
            const panelVisible = panelRect.height > 0 &&
                window.getComputedStyle(this.advancedSearchPanel).display !== 'none' &&
                window.getComputedStyle(this.advancedSearchPanel).visibility !== 'hidden';

            // If panel isn't visible, keep buttons fixed
            if (!panelVisible) {
                if (!this.floatingButtonContainer.classList.contains('floatingButtons-fab-fixed-wrapper')) {
                    this.floatingButtonContainer.classList.remove('floatingButtons-fab-scrollable-wrapper');
                    this.floatingButtonContainer.classList.add('floatingButtons-fab-fixed-wrapper');
                }
                return;
            }

            const buttonAreaHeight = 90;
            const viewportHeight = window.innerHeight;
            const buttonZoneTop = viewportHeight - buttonAreaHeight;

            // If panel extends into the button zone, make buttons scroll
            const wouldOverlap = panelRect.bottom > buttonZoneTop + 75;

            if (!wouldOverlap) {
                if (!this.floatingButtonContainer.classList.contains('floatingButtons-fab-scrollable-wrapper')) {
                    this.floatingButtonContainer.classList.remove('floatingButtons-fab-fixed-wrapper');
                    this.floatingButtonContainer.classList.add('floatingButtons-fab-scrollable-wrapper');
                }
            } else {
                if (!this.floatingButtonContainer.classList.contains('floatingButtons-fab-fixed-wrapper')) {
                    this.floatingButtonContainer.classList.remove('floatingButtons-fab-scrollable-wrapper');
                    this.floatingButtonContainer.classList.add('floatingButtons-fab-fixed-wrapper');
                }
            }
        });
    }

    show() {
        this.floatingButtonContainer.style.visibility = "visible";
    }

    hide() {
        this.floatingButtonContainer.style.visibility = "hidden";
    }

    toggleMenu() {
        queueMicrotask(() => {
            this.menuOpen = !this.menuOpen;
            this.fabMenu.style.display = this.menuOpen ? "flex" : "none";
            this.menuToggleButton.setAttribute("aria-expanded", String(this.menuOpen));

            if (this.menuOpen) {
                this.menuItems[0]?.setAttribute("tabindex", "0");
                this.menuItems[0]?.focus();
            } else {
                this.menuItems?.forEach(item => item.setAttribute("tabindex", "-1"));
            }
        });
    }

    closeMenu() {
        if (this.menuOpen) {
            queueMicrotask(() => {
                this.menuOpen = false;
                this.fabMenu.style.display = "none";
                this.menuToggleButton.setAttribute("aria-expanded", "false");
                this.menuItems?.forEach(item => item.setAttribute("tabindex", "-1"));
            });
        }
    }

    enableEditDeleteButtons() {
        document.getElementById("updateFilterButton")?.classList.remove("floatingButtons-disabled");
        document.getElementById("deleteFilterButton")?.classList.remove("floatingButtons-disabled");
    }

    disableEditDeleteButtons() {
        document.getElementById("updateFilterButton")?.classList.add("floatingButtons-disabled");
        document.getElementById("deleteFilterButton")?.classList.add("floatingButtons-disabled");
    }
}