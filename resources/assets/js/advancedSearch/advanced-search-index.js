// Code to open/close the advancedsearch
import { container } from '/js/dist/simpleDIContainer.min.js';

document.addEventListener('DOMContentLoaded', initFilterSidebar);

function initFilterSidebar() {
    const toggleBtn = document.getElementById('toggleFilterBtn');
    const closeBtn = document.getElementById('closeSidebarButton');
    const filterSection = document.getElementById('filterSection');
    const tableSection = document.querySelector('.table-section');
    const floatingButtons = container.resolve("floatingButtons");

    if (!toggleBtn || !filterSection) return;

    // Bind events
    toggleBtn.addEventListener('click', toggleSidebar);
    closeBtn?.addEventListener('click', () => setSidebarState(false));
    document.addEventListener('keydown', handleGlobalKeys);

    // ---- Handlers ----

    function toggleSidebar() {
        const shouldOpen = filterSection.classList.contains('hide');
        setSidebarState(shouldOpen);
    }

    function setSidebarState(open) {
        filterSection.classList.toggle('hide', !open);
        toggleBtn.setAttribute('aria-expanded', open);

        updateFilterButtonText(open);

        if (open) {
            focusFirstElement(filterSection);
            attachTabTrap();
        } else {
            detachTabTrap();
            toggleBtn.focus();
        }
    }

    function updateFilterButtonText(open) {
        const textSpan = toggleBtn.querySelector('.filter-btn-text');
        const translations = container.resolve("advancedSearchTranslations");
        textSpan.innerText = open ?
            translations.general_close_filters :
            translations.general_open_filters;

        if (open) {
            floatingButtons.show();
            floatingButtons.align();
        } else {
            floatingButtons.hide();
        }
    }

    // ---- Focus Management ----
    function focusFirstElement(container) {
        requestAnimationFrame(() => {
            const firstFocusable = container.querySelector(
                'button, [href], input, select, textarea, [tabindex="0"]'
            );
            firstFocusable?.focus();
        });
    }

    function attachTabTrap() {
        const last = getLastFocusable(filterSection);
        last?.addEventListener('keydown', trapTabForward);
    }

    function detachTabTrap() {
        const last = getLastFocusable(filterSection);
        last?.removeEventListener('keydown', trapTabForward);
    }

    function trapTabForward(e) {
        if (e.key === 'Tab' && !e.shiftKey) {
            e.preventDefault();
            const firstTableFocusable = tableSection?.querySelector(
                'button, [href], input, select, textarea, [tabindex="0"]'
            );
            firstTableFocusable?.focus();
        }
    }

    function getLastFocusable(container) {
        const items = container.querySelectorAll(
            'button, [href], input, select, textarea, [tabindex="0"]'
        );
        return items[items.length - 1];
    }

    // ---- Accessibility ----
    function handleGlobalKeys(e) {
        // ESC closes the sidebar
        if (e.key === 'Escape' && !filterSection.classList.contains('hide')) {
            setSidebarState(false);
        }
    }
}