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

class DateFilterInput extends FilterInput {
    constructor(el, apiService) {
        super(el, apiService);

        // assign as class properties
        this.startDatepickerInput = document.getElementById(el.id + "_start");
        this.endDatepickerInput = document.getElementById(el.id + "_end");

        if (this.startDatepickerInput) {
            this.startDatepicker = $(this.startDatepickerInput).datepicker({
                todayBtn: "linked",
                clearBtn: true,
                disableTouchKeyboard: true,
                forceParse: false,
                keepEmptyValues: true,
                daysOfWeekHighlighted: "0,6",
                todayHighlight: true,
                format: "yyyy-mm-dd",
            });
        }

        if (this.endDatepickerInput) {
            this.endDatepicker = $(this.endDatepickerInput).datepicker({
                todayBtn: "linked",
                clearBtn: true,
                disableTouchKeyboard: true,
                forceParse: false,
                keepEmptyValues: true,
                daysOfWeekHighlighted: "0,6",
                todayHighlight: true,
                format: "yyyy-mm-dd",
            });
        }

        // event listeners (check element exists)
        if (this.startDatepickerInput) {
            $(this.startDatepickerInput).on('changeDate', (event) => {
                this.startDate = new Intl.DateTimeFormat('en-CA').format(event.date);
            });

            $(this.startDatepickerInput).on('clearDate', (_) => {
                this.startDate = undefined;
            });
        }

        if (this.endDatepickerInput) {
            $(this.endDatepickerInput).on('changeDate', (event) => {
                this.endDate = new Intl.DateTimeFormat('en-CA').format(event.date);
            });

            $(this.endDatepickerInput).on('clearDate', (_) => {
                this.endDate = undefined;
            });
        }
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

    setValue(newValue, logic, operator) {

        return new Promise((resolve, reject) => {
            try {

                if (newValue.startDate != undefined) {
                    const startDateObject = new Date(newValue.startDate);
                    this.startDatepicker.datepicker('setDate', startDateObject);
                }

                if (newValue.endDate != undefined) {
                    const endDateObject = new Date(newValue.endDate);
                    this.endDatepicker.datepicker('setDate', endDateObject);
                }

                this.setSearchOperator(logic, operator);
                resolve(newValue);
            }
            catch (e) {
                console.error('Error setting dates:', e);
                reject(e);
            }
        });
    }

    clear() {
        const r = this.getValue();
        if (r.startDate !== undefined || jQuery.isEmptyObject(r) === false) {
            this.startDatepicker.datepicker('clearDates');
            this.startDate = undefined;
        }
        if (r.endDate !== undefined || jQuery.isEmptyObject(r) === false) {
            this.endDatepicker.datepicker('clearDates');
            this.endDate = undefined;
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

class AssignedEntityFilterInput extends TextFilterInput {
    getValue() {
        const value = this.hasValue() ? this.element.value : null;
        const type = document.getElementById(this.element.id + "_type").value;

        if (!value || !type) {
            return;
        }

        return {
            type: type,
            value: value
        }

    }

    setValue(newValue, logic, operator) {
        return new Promise((resolve, reject) => {
            try {
                queueMicrotask(() => {
                    this.element.value = newValue.value;
                    document.getElementById(this.element.id + "_type").value = newValue.type;
                    this.setSearchOperator(logic, operator)
                });

            }
            catch (e) {
                reject(e);
            }
            resolve(newValue);
        })
    } 

    clear() {
        this.element.value = "";
        document.getElementById(this.element.id + "_type").value = "";
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