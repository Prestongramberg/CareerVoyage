import * as actionTypes from "../actions/actionTypes";

export default (state = {}, action) => {
    switch (action.type) {
        case actionTypes.EDUCATORS_LOADING:
            return {
                ...state,
                loading: true
            };
        case actionTypes.EDUCATORS_LOADING_SUCCESS:
        case actionTypes.EDUCATORS_LOADING_FAILURE:
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
        case actionTypes.SCHOOL_QUERY_CHANGED:
            return {
                ...state,
                school: action.school
            };
        case actionTypes.COURSE_QUERY_CHANGED:
            return {
                ...state,
                course: action.course
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
