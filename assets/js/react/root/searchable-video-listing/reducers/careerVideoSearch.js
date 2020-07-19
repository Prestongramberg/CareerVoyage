import * as actionTypes from "../actions/actionTypes";

export default (state = {}, action) => {
    switch (action.type) {
        case actionTypes.CAREER_VIDEO_SEARCH_QUERY_CHANGED:
            return {
                ...state,
                query: action.query
            };
        case actionTypes.CAREER_VIDEO_INDUSTRY_QUERY_CHANGED:
            return {
                ...state,
                industry: action.industry
            };
        default:
            return state;
    }
};
