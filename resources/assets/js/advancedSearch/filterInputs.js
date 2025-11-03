class FilterInput {
    constructor(element, apiService) {
        this.element = element;
        this.apiService = apiService;
    }

    get key() {
        return this.element.id
            .replace("advancedSearch_", "")
            .replace("_input", "");
    }

    setSearchOperator(logic, operator) {
        const data = this.element.id.replace("advancedSearch_", "").replace("_input", "").replace("_start", "").replace("_end", "");
        const filterOptionsDropdown = document.querySelector('[data-field="' + data + '"]');
        filterOptionsDropdown.value = logic + "_" + operator;
    }

    hasValue() {
        return Boolean(this.element.value);
    }

    getValue() {
        throw new Error("getValue() must be implemented by subclass");
    }

    setValue(newValue, logic, operator) {
        return new Promise((resolve, reject) => {
            try {
                queueMicrotask(() => {
                    this.element.value = newValue;
                    this.setSearchOperator(logic, operator)
                });

            }
            catch (e) {
                reject(e);
            }
            resolve(newValue);
        })
    }

    getType() {
        return this.element.id
            .replace("advancedSearch_", "")
            .replace("_input", "")
            .replace("_start", "")
            .replace("_end", "");
    }

    appendTo(filters) {
        const value = this.getValue();

        // Skip empty values
        const isEmptyArray = Array.isArray(value) && value.length === 0;
        const isArrayOfEmptyStrings = Array.isArray(value) && value.every(v => v === "");
        const isTrulyEmpty = value === null || value === undefined || jQuery.isEmptyObject(value) === true;

        if (isTrulyEmpty || isEmptyArray || isArrayOfEmptyStrings) {
            return;
        }

        const field = this.key;

        const basefield = field
            .replace("_start", "")
            .replace("_end", "");

        const filterOptionSelect = document.querySelector(`.filter-option[data-field="${basefield}"]`)

        if (!filterOptionSelect) {
            const isDateRange = field.endsWith('_start') || field.endsWith('_end');
            if (!isDateRange) {
                console.warn(`No filter option select found for field: ${field}`);
            }
            return;
        }

        let operator = "contains";
        let logic = "AND";
        switch (filterOptionSelect.value) {
            case "AND_equals":
                operator = "equals";
                logic = "AND";
                break;
            case "AND_contains":
                operator = "contains";
                logic = "AND";
                break;
            case "NOT_equals":
                operator = "equals";
                logic = "NOT"
                break;
            case "NOT_contains":
                operator = "contains";
                logic = "NOT";
                break;
        }

        filters.push({
            field,
            value,
            operator,
            logic
        })
    }

    clear() {
        // Reset filter options
        const data = this.element.id.replace("advancedSearch_", "").replace("_input", "").replace("_start", "").replace("_end", "");
        const filterOptionsDropdown = document.querySelector('[data-field="' + data + '"]');

        if (filterOptionsDropdown && filterOptionsDropdown.value) {
            filterOptionsDropdown.value = "AND_equals";
        } else {
            console.warn("No filterOptionsDropdown found with datafield " + data);
        }

    }
}

class SelectFilterInput extends FilterInput {

    getValue() {
        const selections = $(this.element).select2('data');

        const selectedValues = selections.map(item => {
            const parseId = parseInt(item.id);
            return isNaN(parseId) ? item.id : parseId;
        })

        if (selectedValues.length === 0) {
            return null;
        }

        return selectedValues;
    }

    setValue(newValues, logic, operator, type = this.getType()) {
        const requestPromises = newValues.map((newValue) => {
            // If it's a number, fetch from backend
            if (typeof newValue === "number") {
                return this.apiService.fetchItemFromBackendById(type, newValue)
                    .then((response) => {
                        return response.json().then((responseJson) => {
                            const $existingOption = $(this.element).find(`option[value='${responseJson.id}']`);

                            if ($existingOption.length === 0) {
                                const option = new Option(responseJson.name, responseJson.id, true, true);
                                $(this.element).append(option);
                            } else {
                                $existingOption.prop('selected', true);
                            }

                            this.setSearchOperator(logic, operator);

                            $(this.element).trigger('change');
                            return responseJson;
                        });
                    });
            } else {
                queueMicrotask(() => {
                    // Directly insert/select string value
                    this.setSearchOperator(logic, operator);
                    const existingOption = $(this.element).find(`option[value='${newValue}']`);

                    if (existingOption.length === 0) {
                        const option = new Option(newValue, newValue, true, true);
                        $(this.element).append(option);
                    } else {
                        existingOption.prop('selected', true);
                    }

                    $(this.element).trigger('change');

                    // Return a resolved promise for consistency
                    return Promise.resolve({ id: newValue, name: newValue });
                });
            }
        });

        return Promise.all(requestPromises);
    }



    clear() {
        $(this.element).val(null).trigger('change');
        super.clear();
    }
}

class AssignedEntityFilterInput extends SelectFilterInput {
    getValue() {
        const selections = $(this.element).select2('data');

        if (!selections.length) return null;

        return selections.map(selection => {
            // Find the corresponding <option> element
            const option = $(this.element).find(`option[value="${selection.id}"]`)[0];

            // Default assignedType in case data-attribute isn't set
            let assignedType = null;

            if (option) {
                assignedType = option.getAttribute('data-assigned-type');
            }

            // If data-assigned-type is missing, fallback to 'type' from Select2 selection (if available)
            if (!assignedType && selection.type) {
                assignedType = "App\\Models\\" + selection.type.charAt(0).toUpperCase() + selection.type.slice(1);
            }

            return {
                assignedType,
                assigned_to: parseInt(selection.id)
            };
        });
    }

    setValue(newValues, logic, operator, type = this.getType()) {
        // Map each new value to a fetch request
        let requestPromises = newValues.map((newValue) => {
            return this.apiService.fetchItemFromBackendById(type, newValue);
        });

        // Wait for all fetches to complete
        return Promise.all(requestPromises).then((responses) => {
            // For each response, parse JSON and prepare to update select options
            let appendPromises = responses.map(response =>
                response.json().then(responseJson => {
                    // Check if option with this ID already exists
                    let $existingOption = $(this.element).find(`option[value='${responseJson.id}']`);

                    if ($existingOption.length === 0) {
                        // Option does not exist, create and append new one (selected)
                        let option = new Option(responseJson.name, responseJson.id, true, true);
                        $(this.element).append(option);
                    } else {
                        // Option exists, mark it selected (in case it was not)
                        $existingOption.prop('selected', true);
                    }
                })
            );

            // Wait for all JSON processing and DOM updates to finish
            return Promise.all(appendPromises).then(() => {
                this.setSearchOperator(logic, operator)

                // Trigger change event once, so Select2 updates UI properly
                $(this.element).trigger('change');
            });
        });
    }

}

class DateFilterInput extends FilterInput {

    constructor(el, apiService) {
        super(el, apiService);

        this.container = this.element.closest('.input-daterange');

        let datepicker = null;
        if (this.container) {
            datepicker = $(this.container).datepicker({
                todayBtn: "linked",
                clearBtn: true,
                disableTouchKeyboard: true,
                forceParse: false,
                keepEmptyValues: true,
                daysOfWeekHighlighted: "0,1",
                todayHighlight: true
            });
        }

        datepicker.on('changeDate', (event) => {
            if (event.target.id.endsWith("_start")) {
                this.startDate = new Intl.DateTimeFormat('en-CA').format(event.date);
            } else {
                this.endDate = new Intl.DateTimeFormat('en-CA').format(event.date);
            }
        });

        datepicker.on('clearDate', (_) => {
            this.startDate = undefined;
            this.endDate = undefined;
        });
    }

    getValue() {
        const result = {};

        if (this.startDate != undefined) {
            result.startDate = this.startDate;
        }
        if (this.endDate != undefined) {
            result.endDate = this.endDate;
        }
        return result;
    }

    /*setValue() {
        $(this.container).datepicker('setStartDate', '2020-01-01');
        //console.error("Currently not implemented");
        //throw new Error("Currently not implemented");
        //return this.hasValue() ? this.element.value : null;
    }*/

    setValue(newValue, logic, operator) {
        return new Promise((resolve, reject) => {
            try {
                queueMicrotask(() => {
                    if (this.startDate != undefined) {
                        $(this.container).datepicker('setStartDate', this.startDate);
                    }
                    if (this.endDate != undefined) {
                        $(this.container).datepicker('setEndDate', this.endDate);
                    }
                });

                this.setSearchOperator(logic, operator)
            }
            catch (e) {
                reject(e);
            }
            resolve(newValue);
        })
    }

    clear() {        
        const r = this.getValue();
        if(r.startDate !== undefined || r.endDate !== undefined) {
            // I don't know why this is needed but without it won't reset properly
            $(this.container).datepicker('setDates', ['2000-01-01', '2000-01-02']);
            $(this.container).datepicker('clearDates');
        }

        super.clear();
    }
}

class TextFilterInput extends FilterInput {
    getValue() {
        return this.hasValue() ? this.element.value : null;
    }
    clear() {
        this.element.value = "";
        super.clear();
    }
}

export {
    FilterInput,
    SelectFilterInput,
    AssignedEntityFilterInput,
    DateFilterInput,
    TextFilterInput
}