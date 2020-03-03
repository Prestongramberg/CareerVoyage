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
        case actionTypes.COMPANIES_LOADING:
            return {
                ...state,
                loading: true,
                loadingCompanies: true
            };
        case actionTypes.COMPANIES_LOADING_SUCCESS:
        case actionTypes.COMPANIES_LOADING_FAILURE:
            return {
                ...state,
                loading: !!state.loadingUser,
                loadingCompanies: false
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
