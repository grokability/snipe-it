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
        this.apiService = container.resolve("apiService");
    }

    async collectFilterInputs() {
        this.inputs = [];

        const tasks = [];

        // Select2
        document.querySelectorAll('select[id^="advancedSearch_"]:not(.no-select2)').forEach(el => {
            tasks.push(new Promise(resolve => {
                setTimeout(() => {
                    this.inputs.push(new SelectFilterInput(el, this.apiService));
                    resolve();
                },0);
            }));
        });

        // Dates
        document.querySelectorAll(
            '.input-daterange.input-group.date-range-input'
        ).forEach(el => {
            this.inputs.push(new DateFilterInput(el, this.apiService));
        });

        // Text
        document.querySelectorAll('input[id^="advancedSearch_"][type="text"]').forEach(el => {
            tasks.push(new Promise(resolve => {
                queueMicrotask(() => {

                    // Skip daterangefields
                    if(el.classList.contains("input-daterange-field")) {
                        return resolve();
                    }

                    // AssignedTo / CheckedOutTo-fields
                    if(el.classList.contains("advancedSearch_polymorphicItemFormatter")) {
                        this.inputs.push(new AssignedEntityFilterInput(el, this.apiService));
                        return resolve();
                    }
                
                    this.inputs.push(new TextFilterInput(el, this.apiService));
                    resolve();
                });
            
            }));
        });
        await Promise.all(tasks);
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
                field.clear();
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
            for (const field of fields) {
                field.disabled = state;
            }
        });
    }
}