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
            
            window.addEventListener("resize", () => this.align());
            window.addEventListener("orientationchange", () => this.align());
            window.addEventListener("load", () => this.align());
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
