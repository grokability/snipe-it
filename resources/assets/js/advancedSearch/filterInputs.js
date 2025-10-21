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

    hasValue() {
        return Boolean(this.element.value);
    }

    getValue() {
        throw new Error("getValue() must be implemented by subclass");
    }

    setValue(newValue) {
        return new Promise((resolve, reject) => {
            try {
                this.element.value = newValue;
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
        if (value === null || value === undefined || value === '') {
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
        const data = this.element.id.replace("advancedSearch_", "").replace("_start", "").replace("_end", "");
        const filterOptionsDropdown = document.querySelector('[data-field="' + data + '"]');

        if (filterOptionsDropdown && filterOptionsDropdown.value) {
            filterOptionsDropdown.value = "AND_contains";
        } else {
            console.warn("No filterOptionsDropdown found with datafield " + data);
        }

        console.log(data);
        //console.log(filterOptionsDropdown);
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

    setValue(newValues, type = this.getType()) {
        const requestPromises = newValues.map((newValue) => {
            return this.apiService.fetchItemFromBackendById(type, newValue);
        });

        return Promise.all(requestPromises)
            .then((responses) => {
                // Map each response to its parsed JSON and DOM manipulation
                const jsonProcessingPromises = responses.map((response) =>
                    response.json().then((responseJson) => {
                        // Check if option already exists
                        const $existingOption = $(this.element).find(`option[value='${responseJson.id}']`);

                        if ($existingOption.length === 0) {
                            // Option doesn't exist, create and append it
                            const option = new Option(responseJson.name, responseJson.id, true, true);
                            $(this.element).append(option);
                        } else {
                            // Option exists, just select it
                            $existingOption.prop('selected', true);
                        }

                        $(this.element).trigger('change');
                        return responseJson;
                    })
                );

                return Promise.all(jsonProcessingPromises); // Wait for all `.json()` parsing to finish
            });
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

    setValue(newValues, type = this.getType()) {
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
                // Trigger change event once, so Select2 updates UI properly
                $(this.element).trigger('change');
            });
        });
    }

}


class DateFilterInput extends FilterInput {
    getValue() {
        return this.hasValue() ? this.element.value : null;
    }

    clear() {
        this.element.value = "";
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