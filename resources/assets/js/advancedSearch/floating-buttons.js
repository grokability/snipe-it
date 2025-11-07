// resources/js/modules/floating-buttons.js
export default class FloatingButtons {
    constructor() {
        this.advancedSearchPanel = document.getElementById("advancedSearchPanel");
        this.floatingButtonContainer = document.getElementById("floatingButtonContainer");
        this.menuToggleButton = document.getElementById("menuToggleButton");
        this.fabMenu = document.getElementById("fabMenu");
        this.menuItems = this.fabMenu?.querySelectorAll("[role='menuitem']");
        this.menuOpen = false;

        // rAF throttle flag
        this._ticking = false;

        // store original parent/sibling to restore when switching back to fixed
        this._originalParent = this.floatingButtonContainer?.parentNode || document.body;
        this._originalNextSibling = this.floatingButtonContainer?.nextSibling || null;

        // remember if we changed panel positioning so we can avoid overwriting styles
        this._panelPositionWasStatic = false;

        // saved inline height so we can restore when switching back to fixed mode
        this._savedPanelInlineHeight = this.advancedSearchPanel ? this.advancedSearchPanel.style.height || '' : '';

        // buffer used when making the panel taller to accommodate the floating buttons
        this._heightBuffer = 70;

        // measured content height (natural)
        this.advancedSearchPanelHeight = this.advancedSearchPanel ? this.advancedSearchPanel.scrollHeight : 0;
        this.advancedSearchPanelExtended = false;

        this.init();
    }

    init() {
        if (!this.floatingButtonContainer) return;

        // Ensure we have an inner wrapper to animate transforms/opacity
        this._ensureInnerWrapper();

        this.bindEvents();
        this.align();
        this.updatePositionMode();
        // refresh stored natural height
        this.advancedSearchPanelHeight = this._getNaturalPanelHeight();

        // observe changes if sidepanel is open / closed
        this._observePanelSize();
    }



    _observePanelSize() {
    if (!this.advancedSearchPanel) return;

    let lastWidth = this.advancedSearchPanel.offsetWidth;
    let lastHeight = this.advancedSearchPanel.offsetHeight;

    const ro = new ResizeObserver(entries => {
        for (const entry of entries) {
            const newWidth = entry.contentRect.width;
            const newHeight = entry.contentRect.height;

            // Only trigger when size changes (with / height)
            if (newWidth !== lastWidth || newHeight !== lastHeight ) {
                lastWidth = newWidth;
                lastHeight = newHeight;
                
                this.align();
                this.updatePositionMode();
            }
        }
    });

    ro.observe(this.advancedSearchPanel);
}

    // Wrap existing children in a .floatingButtons-inner so we can animate transforms
    _ensureInnerWrapper() {
        if (!this.floatingButtonContainer) return;

        const existing = this.floatingButtonContainer.querySelector('.floatingButtons-inner');
        if (existing) {
            this.inner = existing;
            return;
        }

        const inner = document.createElement('div');
        inner.className = 'floatingButtons-inner';

        while (this.floatingButtonContainer.firstChild) {
            inner.appendChild(this.floatingButtonContainer.firstChild);
        }

        this.floatingButtonContainer.appendChild(inner);
        this.inner = inner;

        // re-query nodes moved into inner
        this.menuToggleButton = this.floatingButtonContainer.querySelector('#menuToggleButton') || this.menuToggleButton;
        this.fabMenu = this.floatingButtonContainer.querySelector('#fabMenu') || this.fabMenu;
        this.menuItems = this.fabMenu?.querySelectorAll("[role='menuitem']");
    }

    bindEvents() {
        queueMicrotask(() => {
            // Menu events
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
                    try { this.menuToggleButton.focus(); } catch (err) { }
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

            // Window events — schedule with rAF for smoothness
            const scheduleFullUpdate = () => {
                if (!this._ticking) {
                    this._ticking = true;
                    window.requestAnimationFrame(() => {
                        this.align();
                        this.updatePositionMode();
                        this._ticking = false;
                    });
                }
            };

            window.addEventListener("resize", scheduleFullUpdate, { passive: true });
            window.addEventListener("orientationchange", scheduleFullUpdate, { passive: true });
            window.addEventListener("load", scheduleFullUpdate, { passive: true });

            window.addEventListener("scroll", () => {
                // on scroll we only need to check the mode; throttle via rAF
                if (!this._ticking) {
                    this._ticking = true;
                    window.requestAnimationFrame(() => {
                        this.updatePositionMode();
                        this._ticking = false;
                    });
                }
            }, { passive: true });
        });
    }

    align() {
        if (!this.advancedSearchPanel || !this.floatingButtonContainer) return;

        queueMicrotask(() => {
            const panelRect = this.advancedSearchPanel.getBoundingClientRect();
            const scrollLeft = window.pageXOffset || document.documentElement.scrollLeft;
            const centerX = panelRect.left + (panelRect.width / 2) + scrollLeft;

            // Only set absolute page-based left when the container is in the document root (fixed mode).
            if (this.floatingButtonContainer.classList.contains('floatingButtons-fab-fixed-wrapper')) {
                this.floatingButtonContainer.style.left = `${centerX}px`;
                this.floatingButtonContainer.style.transform = "translateX(-50%)";
            } else {
                // when positioned inside the panel (absolute), use left:50% + translateX(-50%) to center relative to panel
                this.floatingButtonContainer.style.left = '50%';
                this.floatingButtonContainer.style.transform = "translateX(-50%)";
            }
        });
    }

    updatePositionMode() {
        if (!this.advancedSearchPanel || !this.floatingButtonContainer) return;

        // always refresh natural height (don't rely on stale stored value)
        this.advancedSearchPanelHeight = this._getNaturalPanelHeight();

        queueMicrotask(() => {
            const panelRect = this.advancedSearchPanel.getBoundingClientRect();

            // Check if panel is actually visible and has height
            const panelVisible = panelRect.height > 0 &&
                window.getComputedStyle(this.advancedSearchPanel).display !== 'none' &&
                window.getComputedStyle(this.advancedSearchPanel).visibility !== 'hidden';

            // If panel isn't visible, force fixed
            if (!panelVisible) {
                this._setFixedMode();
                return;
            }

            const buttonAreaHeight = 90;
            const viewportHeight = window.innerHeight;
            const buttonZoneTop = viewportHeight - buttonAreaHeight;

            // If panel extends into the button zone, make buttons fixed; otherwise make them scrollable (absolute inside panel)
            const wouldOverlap = panelRect.bottom > buttonZoneTop + 250;

            const minScrollableHeight = 400; // tweak as needed to match your layout
            if (panelRect.height < minScrollableHeight) {
                this._setScrollableMode();
                return;
            }

            if (!wouldOverlap) {
                this._setScrollableMode();
            } else {
                this._setFixedMode();
            }
        });
    }

    // helper to get the natural content height of the panel without any inline height applied
    _getNaturalPanelHeight() {
        if (!this.advancedSearchPanel) return 0;
        const prevInline = this.advancedSearchPanel.style.height;
        // Temporarily clear inline height to measure natural content height
        this.advancedSearchPanel.style.height = '';
        const natural = this.advancedSearchPanel.scrollHeight;
        // restore previous inline height
        this.advancedSearchPanel.style.height = prevInline;
        return natural;
    }

    // Move the floating container inside the panel and set class for absolute positioning
    _setScrollableMode() {
        if (!this.floatingButtonContainer || !this.advancedSearchPanel) return;

        // // if already scrollable, nothing to do
        // if (this.floatingButtonContainer.classList.contains('floatingButtons-fab-scrollable-wrapper')) {
        //     console.log('abort'); 
        //     return;
        // }

        // ensure panel can be a positioned ancestor
        const panelStyle = window.getComputedStyle(this.advancedSearchPanel);
        if (panelStyle.position === 'static') {
            this._panelPositionWasStatic = true;
            this.advancedSearchPanel.style.position = 'relative';
        }

        // move container into the panel so position:absolute makes it scroll with the panel
        this.advancedSearchPanel.appendChild(this.floatingButtonContainer);

        // switch classes
        this.floatingButtonContainer.classList.remove('floatingButtons-fab-fixed-wrapper');
        this.floatingButtonContainer.classList.add('floatingButtons-fab-scrollable-wrapper');

        // measure natural content height and ensure there's extra space for the buttons to sit comfortably
        const naturalHeight = this._getNaturalPanelHeight();
        this.advancedSearchPanelHeight = naturalHeight;

        this.advancedSearchPanelExtended = false;
        
        // to Ensure there is everytime a minheight for the buttons when the advancedSearchPanel is very small
        const minHeight = 0;
        const newHeight = Math.max(minHeight, naturalHeight + this._heightBuffer);
        this.advancedSearchPanel.style.height = `${newHeight}px`;
        // this.advancedSearchPanel.style.height = `${Math.max(0, naturalHeight + this._heightBuffer)}px`;
    }

    // Move the floating container back to its original parent and set fixed class
    _setFixedMode() {
        if (!this.floatingButtonContainer) return;

        // if already fixed, nothing to do
        if (this.floatingButtonContainer.classList.contains('floatingButtons-fab-fixed-wrapper')) {
            return;
        }

        // restore panel position style if we changed it earlier
        if (this._panelPositionWasStatic && this.advancedSearchPanel) {
            this.advancedSearchPanel.style.position = '';
            this._panelPositionWasStatic = false;
        }

        // restore container to original parent in original position
        try {
            if (this._originalNextSibling && this._originalNextSibling.parentNode === this._originalParent) {
                this._originalParent.insertBefore(this.floatingButtonContainer, this._originalNextSibling);
            } else {
                this._originalParent.appendChild(this.floatingButtonContainer);
            }
        } catch (e) {
            // fallback: append to body
            document.body.appendChild(this.floatingButtonContainer);
        }

        // switch classes
        this.floatingButtonContainer.classList.remove('floatingButtons-fab-scrollable-wrapper');
        this.floatingButtonContainer.classList.add('floatingButtons-fab-fixed-wrapper');

        // align using panel metrics to compute fixed-left position
        const panelRect = this.advancedSearchPanel ? this.advancedSearchPanel.getBoundingClientRect() : null;
        if (panelRect) {
            const scrollLeft = window.pageXOffset || document.documentElement.scrollLeft;
            const centerX = panelRect.left + (panelRect.width / 2) + scrollLeft;
            this.floatingButtonContainer.style.left = `${centerX}px`;
            this.floatingButtonContainer.style.transform = "translateX(-50%)";
        }

        // restore the panel's original inline height (if we saved one) so we don't keep a reduced height
        if (this.advancedSearchPanel) {
            this.advancedSearchPanelExtended = true;
            // restore saved inline height (may be empty string, which will clear the inline style)
            this.advancedSearchPanel.style.height = this._savedPanelInlineHeight || '';
        }
    }

    // Public setter so other code can explicitly set the mode
    setFloatingMode(mode) {
        if (mode === 'scrollable') {
            this._setScrollableMode();
        } else {
            this._setFixedMode();
        }
    }

    show() {
        if (!this.floatingButtonContainer) return;
        this.floatingButtonContainer.style.visibility = "visible";
    }

    hide() {
        if (!this.floatingButtonContainer) return;
        this.floatingButtonContainer.style.visibility = "hidden";
    }

    toggleMenu() {
        queueMicrotask(() => {
            this.menuOpen = !this.menuOpen;

            if (this.menuOpen) {
                this.fabMenu?.classList.add('open');
                this.fabMenu && this.fabMenu.setAttribute('aria-hidden', 'false');
                this.menuToggleButton?.setAttribute("aria-expanded", "true");

                this.menuItems?.forEach((item, idx) => {
                    item.setAttribute("tabindex", "0");
                });
                this.menuItems?.[0]?.focus();
            } else {
                this.fabMenu?.classList.remove('open');
                this.fabMenu && this.fabMenu.setAttribute('aria-hidden', 'true');
                this.menuToggleButton?.setAttribute("aria-expanded", "false");

                this.menuItems?.forEach(item => item.setAttribute("tabindex", "-1"));
            }
        });
    }

    closeMenu() {
        if (!this.menuOpen) return;
        queueMicrotask(() => {
            this.menuOpen = false;
            this.fabMenu?.classList.remove('open');
            this.fabMenu && this.fabMenu.setAttribute('aria-hidden', 'true');
            this.menuToggleButton?.setAttribute("aria-expanded", "false");
            this.menuItems?.forEach(item => item.setAttribute("tabindex", "-1"));
        });
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