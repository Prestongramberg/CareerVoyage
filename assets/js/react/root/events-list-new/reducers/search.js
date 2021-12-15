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
                searchQuery: action.searchQuery,
                refetchEvents: true
            };
        case actionTypes.PRIMARY_INDUSTRY_QUERY_CHANGED:
            return {
                ...state,
                industry: action.industry,
                secondaryIndustry: '',
                refetchEvents: true
            };
        case actionTypes.EVENTS_REFRESHED:
            return {
                ...state,
                refetchEvents: false
            };
        case actionTypes.SECONDARY_INDUSTRY_QUERY_CHANGED:
            return {
                ...state,
                secondaryIndustry: action.industry,
                refetchEvents: true
            };
        case actionTypes.EVENT_TYPE_QUERY_CHANGED:
            return {
                ...state,
                eventType: action.eventType,
                refetchEvents: true
            }
        case actionTypes.COMPANY_QUERY_CHANGED:
            return {
                ...state,
                company: action.company
            };
        case actionTypes.RADIUS_CHANGED:
            return {
                ...state,
                radius: action.radius,
                refetchEvents: true
            };
        case actionTypes.ZIPCODE_CHANGED:
            return {
                ...state,
                zipcode: action.zipcode,
                refetchEvents: true
            };
        default:
            return state;
    }
};
