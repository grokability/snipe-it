import ApiService from '/js/dist/apiService.min.js';
import FilterFormManager from '/js/dist/filterFormManager.min.js';
import FilterUIController from '/js/dist/filterUiController.min.js';
import FloatingButtons from '/js/dist/floating-buttons.min.js';
import { container } from '/js/dist/simpleDIContainer.min.js';

export default function initAdvancedSearch(config = {}) {
    // register services (do not run on import side-effects)
    container.register("apiService", new ApiService());
    container.register("filterFormManager", new FilterFormManager());
    container.register("floatingButtons", new FloatingButtons());

    const sleep = (ms) => new Promise(r => setTimeout(r, ms));

    document.addEventListener('livewire:init', function () {
        const tableId = config.tableId;
        const $table = $('#' + tableId);

        const controller = new FilterUIController($table);
        container.register("filterUiController", controller);
        controller.bindEvents();

        if (config.predefinedFilterId) {
            controller.updateFilterWithPredefined(null, config.predefinedFilterId);

            const option = new Option(String(config.predefinedFilterName || ''), config.predefinedFilterId, true, true);
            const sel = document.getElementById("predefinedfilters-select");
            if (sel) sel.append(option);

            const filterSection = document.getElementById('filterSection');
            if (filterSection) filterSection.classList.remove('hide');

        } else {
            setTimeout(async () => {
                const filterSection = document.getElementById('filterSection');
                if (filterSection) filterSection.classList.remove('hide');
                container.resolve("filterFormManager").clearAll();

                await new Promise(resolve => setTimeout(resolve, 0));
                if (filterSection) filterSection.classList.add('hide');
            }, 0);
        }
    });

    // Filter search functionality (guard element existence)
    const input = document.getElementById('filterSearch');
    if (input) {
        input.addEventListener('input', function (e) {
            const searchTerm = e.target.value.toLowerCase();
            const items = document.querySelectorAll('.filter-item');
            items.forEach(item => {
                const label = item.querySelector('label');
                const labelText = label ? label.textContent.toLowerCase() : '';
                item.style.display = labelText.includes(searchTerm) ? '' : 'none';
            });
        });
    }
}
