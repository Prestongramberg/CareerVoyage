import * as actionTypes from "../actions/actionTypes";

export default (state = {}, action) => {
    switch (action.type) {
        case actionTypes.PROFESSIONALS_LOADING:
            return {
                ...state,
                loading: true
            };
        case actionTypes.PROFESSIONALS_LOADING_SUCCESS:
        case actionTypes.PROFESSIONALS_LOADING_FAILURE:
            return {
                ...state,
                loading: false
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
        case actionTypes.COMPANY_QUERY_CHANGED:
            return {
                ...state,
                company: action.company
            };
        default:
            return state;
    }
};
