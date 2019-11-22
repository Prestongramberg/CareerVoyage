import * as actionTypes from "../actions/actionTypes";

export default (state = {}, action) => {
    switch (action.type) {
        case actionTypes.SEARCH_QUERY_CHANGED:
            return {
                ...state,
                query: action.query
            };
        case actionTypes.USER_LOADING_SUCCESS:
        case actionTypes.USER_LOADING_FAILURE:
            return {
                ...state,
                loading: !!state.loadingSchools,
                loadingUser: false
            };
        case actionTypes.SCHOOLS_LOADING_SUCCESS:
        case actionTypes.SCHOOLS_LOADING_FAILURE:
            return {
                ...state,
                loading: !!state.loadingUser,
                loadingSchools: false
            };
        default:
            return state;
    }
};
