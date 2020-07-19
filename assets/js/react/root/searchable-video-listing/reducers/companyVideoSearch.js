import * as actionTypes from "../actions/actionTypes";

export default (state = {}, action) => {
    switch (action.type) {
        case actionTypes.COMPANY_VIDEO_SEARCH_QUERY_CHANGED:
            return {
                ...state,
                query: action.query
            };
        case actionTypes.COMPANY_VIDEO_INDUSTRY_QUERY_CHANGED:
            return {
                ...state,
                industry: action.industry
            };
        case actionTypes.COMPANY_QUERY_CHANGED:
            return {
                ...state,
                company: action.company
            };
        default:
            return state;
    }
};
