import * as actionTypes from "../actions/actionTypes";

export default (state = {}, action) => {
    switch (action.type) {
        case actionTypes.EVENTS_LOADING_SUCCESS:
        case actionTypes.EVENTS_LOADING_FAILURE:
            return {
                ...state,
                loading: false
            };
        case actionTypes.SEARCH_QUERY_CHANGED:
            return {
                ...state,
                query: action.query
            };
        case actionTypes.PRIMARY_INDUSTRY_QUERY_CHANGED:
            return {
                ...state,
                industry: action.industry,
                secondaryIndustry: ''
            };
        case actionTypes.SECONDARY_INDUSTRY_QUERY_CHANGED:
            return {
                ...state,
                secondaryIndustry: action.industry
            };
        case actionTypes.EVENT_TYPE_QUERY_CHANGED:
            return {
                ...state,
                eventType: action.eventType
            }
        case actionTypes.COMPANY_QUERY_CHANGED:
            return {
                ...state,
                company: action.company
            };
        case actionTypes.RADIUS_CHANGED:
            return {
                ...state,
                radius: action.radius
            };
        case actionTypes.ZIPCODE_CHANGED:
            return {
                ...state,
                zipcode: action.zipcode
            };
        default:
            return state;
    }
};
