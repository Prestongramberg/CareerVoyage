
function _get(engineKey, path, params) {
    debugger;
}

function _request(engineKey, path, params) {
    debugger;
}

class ProfessionalSearchAPIConnector {
    /**
     * @callback next
     * @param {Object} updatedQueryOptions The options to send to the API
     */

    /**
     * @callback hook
     * @param {Object} queryOptions The options that are about to be sent to the API
     * @param {next} next The options that are about to be sent to the API
     */

    /**
     * @typedef Options
     * @param  {string} documentType Document Type found in your Site Search Dashboard
     * @param  {string} engineKey Credential found in your Site Search Dashboard
     * @param  {hook} beforeSearchCall=(queryOptions,next)=>next(queryOptions) A hook to amend query options before the request is sent to the
     *   API in a query on an "onSearch" event.
     * @param  {hook} beforeAutocompleteResultsCall=(queryOptions,next)=>next(queryOptions) A hook to amend query options before the request is sent to the
     *   API in a "results" query on an "onAutocomplete" event.
     */

    /**
     * @param {Options} options
     */
    constructor() {
        debugger;
        this.request = _request.bind(this);
        this._get = _get.bind(this);
    }

    onResultClick({ query, documentId, tags }) {
        debugger;
    }

    onAutocompleteResultClick({ query, documentId, tags }) {
       debugger;
    }

    onSearch(state, queryConfig) {
        debugger;

        var promise = new Promise(function(resolve, reject) {
            /* missing implementation */

            let result = {
                results: [
                    {
                        id: {
                            raw: "This is the first example",
                            // A snippet value contains a highlighted value. I.e., 'I <em>am</em> a raw
                            // result'. These are always sanitized and safe to render as html.
                            snippet: "This is the...",
                            acres: "Any"
                        },
                        raw: "This is the first example",
                        // A snippet value contains a highlighted value. I.e., 'I <em>am</em> a raw
                        // result'. These are always sanitized and safe to render as html.
                        snippet: "This is the...",
                        acres: "Any"
                    }
                ],
                totalPages: 1,
                totalResults: 1,
                requestId: 839839232,
                facets: {
                    acres: [
                        {data: [
                                {value: "Any", count: 1}
                            ]}
                    ]
                }
            };


            resolve(result);
        });

        return promise;
    }

    search() {
        debugger;
    }

    async onAutocomplete({ searchTerm }, queryConfig) {
        debugger;
    }
}

export default ProfessionalSearchAPIConnector;
