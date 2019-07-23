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
        case actionTypes.COURSES_LOADING_SUCCESS:
        case actionTypes.COURSES_LOADING_FAILURE:
            return {
                ...state,
                loading: !!state.loadingLessons,
                loadingIndustries: false
            };
        case actionTypes.LESSONS_LOADING_SUCCESS:
        case actionTypes.LESSONS_LOADING_FAILURE:
            return {
                ...state,
                loading: !!state.loadingIndustries,
                loadingLessons: false
            };
        default:
            return state;
    }
};