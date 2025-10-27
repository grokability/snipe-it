import {
    FilterInput,
    SelectFilterInput,
    AssignedEntityFilterInput,
    DateFilterInput,
    TextFilterInput
} from '/js/dist/filterInputs.min.js';
import { container } from '/js/dist/simpleDIContainer.min.js';

export default class FilterFormManager {
    constructor() {
        this.filters = [];
        this.inputs = [];
        this.apiService = container.resolve("apiService");;
    }

    collectFilterInputs() {
        this.inputs = [];

        // Select2
        document.querySelectorAll('select[id^="advancedSearch_"]').forEach(el => {
            queueMicrotask(() => {
                this.inputs.push(new SelectFilterInput(el, this.apiService));
            });
        });

        // Dates
        document.querySelectorAll('input[id^="advancedSearch_"][id$="_start"][type="date"], input[id^="advancedSearch_"][id$="_end"][type="date"]').forEach(el => {
            queueMicrotask(() => {
                this.inputs.push(new DateFilterInput(el, this.apiService));
            });
        });

        // Text
        document.querySelectorAll('input[id^="advancedSearch_"][type="text"]').forEach(el => {
            queueMicrotask(() => {
                this.inputs.push(new TextFilterInput(el, this.apiService));
            });
        });


        return this.inputs;
    }

    collectFilterData() {
        this.filters = [];

        // Process all inputs polymorphically
        this.inputs.forEach(input => {
            input.appendTo(this.filters);
        });

        return this.filters;
    }

    clearAll() {
        //this.collectFilterData();
        this.inputs.forEach(field => {
            queueMicrotask(() => {
                field.clear();
            })
        });
    }

    setValuesFromResponse(responseArray) {
        this.clearAll();

        const promises = [];

        for (const filter of responseArray) {
            const { field: key, value, logic, operator } = filter;

            const field = this.inputs.find(input => input.key === key);
            if (!field) {
                console.warn(`No input found for key: ${key}`);
                continue;
            }

            try {
                const result = field.setValue(value, logic, operator);
                if (result instanceof Promise) {
                    promises.push(result);
                }
            } catch (err) {
                console.error(`Failed to set value for "${key}":`, err);
            }
        }

        return Promise.all(promises)
            .then((results) => {
                this.setAdvancedSearchPanelFilterEnabledState(false);
                return results;
            });
    }

    setAdvancedSearchPanelFilterEnabledState(state) {
        queueMicrotask(() => {
            const fields = document.getElementById("advancedSearchPanel").getElementsByTagName('*');
            for (let i = 0; i < fields.length; i++) {
                fields[i].disabled = state;
            }
        });
    }
}