import {
    FilterInput,
    SelectFilterInput,
    AssignedEntityFilterInput,
    DateFilterInput,
    TextFilterInput
} from '/js/dist/filterInputs.min.js';

export default class FilterFormManager {
    constructor(apiService) {
        this.filters = [];
        this.inputs = [];
        this.apiService = apiService;
    }

    collect() {
        this.filters = [];
        this.inputs = [];

        // Select2
        document.querySelectorAll('select[id^="advancedSearch_"]').forEach(el => {
            if (el.id === 'advancedSearch_assigned_to') {
                this.inputs.push(new AssignedEntityFilterInput(el, this.apiService));
            } else {
                this.inputs.push(new SelectFilterInput(el, this.apiService));
            }
        });

        // Dates
        document.querySelectorAll('input[id^="advancedSearch_"][id$="_start"][type="date"], input[id^="advancedSearch_"][id$="_end"][type="date"]').forEach(el => {
            this.inputs.push(new DateFilterInput(el, this.apiService));
        });

        // Text
        document.querySelectorAll('input[id^="advancedSearch_"][type="text"]').forEach(el => {
            this.inputs.push(new TextFilterInput(el, this.apiService));
        });

        // Process all inputs polymorphically
        this.inputs.forEach(input => {
            input.appendTo(this.filters);
        });

        return this.filters;
    }

    clearAll() {
        this.collect();
        this.inputs.forEach(field => {
            field.clear();
        });
    }

    async setValuesFromResponse(response) {
        this.clearAll();

        const promises = [];

        for (const key in response) {
            const value = response[key];

            const field = this.inputs.find(input => input.key === key);
            if (!field) {
                console.warn(`No input found for key: ${key}`);
                continue;
            }

            try {
                const result = field.setValue(value);
                // If the method returns a promise, store it
                if (result instanceof Promise) {
                    promises.push(result);
                }
            } catch (err) {
                console.error(`Failed to set value for "${key}":`, err);
            }
        }

        // Wait for all async setValue calls to complete
        await Promise.all(promises);
        this.setAdvancedSearchPanelFilterEnabledState(false);
    }

    setAdvancedSearchPanelFilterEnabledState(state) {
        const fields = document.getElementById("advancedSearchPanel").getElementsByTagName('*');
        for (let i = 0; i < fields.length; i++) {
            fields[i].disabled = state;
        }
    }
}