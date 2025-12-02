import { container } from '/js/dist/simpleDIContainer.min.js';

class FilterUIController {
    constructor(tableElement) {
        this.$table = tableElement;
        this.apiService = container.resolve("apiService");
        this.collector = container.resolve("filterFormManager");
        this.collector.collectFilterInputs();
        this.translations = container.resolve("advancedSearchTranslations");

        // store handler references so we can unbind them
        this.handlePredefinedChange = (e) => this.updateFilterWithPredefined(e);
        this.handleFilterClick = this.refresh.bind(this);
        this.handleClearClick = () => {
            this.collector.clearAll();
            $('#predefinedfilters-select').val(null).trigger('change');
        };
        this.handleSaveClick = () => this.storePredefinedFilterInBackend();
        this.handleUpdateClick = (e) => this.updatePredefinedFilterInBackend(e.target.id);
        this.handleDeleteClick = (e) => this.deletePredefinedFilterFromBackend(e.target.id);

    }

    destroy() {
        document.removeEventListener('click', this._boundDocumentClick, true);
        window.removeEventListener('keydown', this._boundKeydown);
        // also null any large references & call other destroy methods (floating buttons, etc)
        this._boundDocumentClick = null;
        this._boundKeydown = null;
    }

    refresh() {
        const filters = this.collector.collectFilterData();
        this.$table.bootstrapTable('refresh', {
            query: {
                filter: JSON.stringify(filters)
            }
        });
    }

    async updateFilterWithPredefined(event, selectedId = null) {
        if (event !== null) {
            selectedId = event?.target?.value;
        }

        const floatingButtons = container.resolve("floatingButtons");

        if (!selectedId) {
            floatingButtons.disableEditDeleteButtons();
            return;
        }

        floatingButtons.enableEditDeleteButtons();

        try {
            const response = await this.apiService.fetchPredefinedFilterData(selectedId);
            if (!response.ok) {
                throw new Error("Network response was not ok");
            }

            const data = await response.json();
            await this.collector.setValuesFromResponse(data.filter_data);
            this.refresh();

        } catch (err) {
            console.error("Failed to apply predefined filter:", err);
            Livewire.dispatch('showNotification', {
                type: 'error',
                message: this.translations.general_failed_to_apply_predefined_filter
            });
        }
    }

    storePredefinedFilterInBackend() {
        const filters = this.collector.collectFilterData();

        if (!filters || filters.length === 0) {
            Livewire.dispatch('showNotification', {
                type: "error",
                title: this.translations.error,
                message: this.translations.general_can_not_save_empty_filter
            });
            return;
        }

        Livewire.dispatch('openPredefinedFiltersModal', {
            action: 'create',
            predefinedFilterData: filters
        });
    }

    updatePredefinedFilterInBackend(updateFilterButtonId) {
        const updateBtn = document.getElementById(updateFilterButtonId);
        if (updateBtn.classList.contains('disabled')) return;

        const selectedFilter = $("#predefinedfilters-select").select2('data')[0];
        if (!selectedFilter) return;

        const filters = this.collector.collectFilterData();

        if (!filters || filters.length === 0) {
            Livewire.dispatch('showNotification', {
                type: "error",
                title: this.translations.error,
                message: this.translations.general_can_not_update_empty_filter
            });
            return;
        }

        Livewire.dispatch('openPredefinedFiltersModal', {
            action: 'edit',
            predefinedFilterId: parseInt(selectedFilter.id),
            predefinedFilterData: filters
        });
    }

    deletePredefinedFilterFromBackend(deleteFilterButtonId) {
        const deleteBtn = document.getElementById(deleteFilterButtonId);
        if (deleteBtn.classList.contains('disabled')) return;

        const selected = $("#predefinedfilters-select").select2('data')[0];
        if (!selected || !selected.id) return;

        Livewire.dispatch('openPredefinedFiltersModal', {
            action: 'delete',
            predefinedFilterId: parseInt(selected.id)
        });
    }

    bindEvents() {
        $('#predefinedfilters-select').on('change', this.handlePredefinedChange);

        const filterButton = document.getElementById("filterButton");
        if (filterButton) {
            filterButton.addEventListener('click', this.handleFilterClick);
        }

        const clearButtons = ["clearInputButton", "topClearInputButton"];
        clearButtons.forEach(id => {
            const btn = document.getElementById(id);
            if (btn) {
                btn.addEventListener('click', this.handleClearClick);
            }
        });

        const saveButton = document.getElementById("storeFilterButton");
        if (saveButton) {
            saveButton.addEventListener('click', this.handleSaveClick);
        }

        const updateButton = document.getElementById("updateFilterButton");
        if (updateButton) {
            updateButton.addEventListener('click', this.handleUpdateClick);
        }

        const deleteButton = document.getElementById("deleteFilterButton");
        if (deleteButton) {
            deleteButton.addEventListener('click', this.handleDeleteClick);
        }
    }

    unbindEvents() {
        $('#predefinedfilters-select').off('change', this.handlePredefinedChange);

        const filterButton = document.getElementById("filterButton");
        if (filterButton) {
            filterButton.removeEventListener('click', this.handleFilterClick);
        }

        const clearButtons = ["clearInputButton", "topClearInputButton"];
        clearButtons.forEach(id => {
            const btn = document.getElementById(id);
            if (btn) {
                btn.removeEventListener('click', this.handleClearClick);
            }
        });

        const saveButton = document.getElementById("storeFilterButton");
        if (saveButton) {
            saveButton.removeEventListener('click', this.handleSaveClick);
        }

        const updateButton = document.getElementById("updateFilterButton");
        if (updateButton) {
            updateButton.removeEventListener('click', this.handleUpdateClick);
        }

        const deleteButton = document.getElementById("deleteFilterButton");
        if (deleteButton) {
            deleteButton.removeEventListener('click', this.handleDeleteClick);
        }
    }
}

export default FilterUIController;