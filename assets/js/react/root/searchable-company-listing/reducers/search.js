import * as actionTypes from "../actions/actionTypes";

export default (state = {}, action) => {
    switch (action.type) {
        case actionTypes.SEARCH_QUERY_CHANGED:
            return {
                ...state,
                query: action.query
            };
        case actionTypes.INDUSTRY_QUERY_CHANGED:
            return {
                ...state,
                industry: action.industry
            };
        case actionTypes.USER_LOADING_SUCCESS:
        case actionTypes.USER_LOADING_FAILURE:
            return {
                ...state,
                loading: !!state.loadingCompanies,
                loadingUser: false
            };
        case actionTypes.COMPANIES_LOADING_SUCCESS:
        case actionTypes.COMPANIES_LOADING_FAILURE:
            return {
                ...state,
                loading: !!state.loadingUser,
                loadingCompanies: false
            };
        default:
            return state;
    }
};