import * as actionTypes from "../actions/actionTypes";

export default (state = {}, action) => {
    switch (action.type) {
        case actionTypes.SEARCH_QUERY_CHANGED:
            return {
                ...state,
                query: action.query
            };
        case actionTypes.COURSE_QUERY_CHANGED:
            return {
                ...state,
                course: action.course
            };
        case actionTypes.USER_LOADING_SUCCESS:
        case actionTypes.USER_LOADING_FAILURE:
            return {
                ...state,
                loading: !!state.loadingLessons,
                loadingUser: false
            };
        case actionTypes.LESSONS_LOADING_SUCCESS:
        case actionTypes.LESSONS_LOADING_FAILURE:
            return {
                ...state,
                loading: !!state.loadingUser,
                loadingLessons: false
            };
        default:
            return state;
    }
};