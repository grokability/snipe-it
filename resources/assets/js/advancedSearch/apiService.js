export default class ApiService {
    constructor(baseUrl = '/api/v1') {
        this.baseUrl = baseUrl;
        this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    }

    fetchItemFromBackendById(type, id) {
        const typeMap = {
            asset: "hardware",
            category: "categories",
            company: "companies",
            location: "locations",
            manufacturer: "manufacturers",
            model: "models",
            groups: "groups",
            group_select: "predefinedFilters",
            rtd_location: "locations",
            status_label: "statuslabels",
            supplier: "suppliers",
            user: "users"
        };

        if (!typeMap[type]) {
            return Promise.reject(`Invalid type ${type}`);
        }
        const path = `${this.baseUrl}/${typeMap[type]}/${id}`;
        return this.fetchFromBackend('GET', path);
    }

    predefinedFilterRequest(method, filterId = null, filterData = null) {
        let path = "${this.baseUrl}/predefinedFilters";

        if (filterId !== null) {
            path += "/" + filterId;
        }

        return this.fetchFromBackend(method, path, filterData);
    }

    fetchPredefinedFilterData(filterId) {
        const path = `${this.baseUrl}/predefinedFilters/${filterId}`;
        return this.fetchFromBackend('GET', path);
    }

    fetchFromBackend(method, path, body = null) {
        const options = {
            method: method,
            headers: {
                accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': this.csrfToken,
                'Content-Type': 'application/json'
            },
            ...(body && { body })
        };

        return fetch(path, options);
    }

}