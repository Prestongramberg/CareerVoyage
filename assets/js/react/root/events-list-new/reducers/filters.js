import * as actionTypes from "../actions/actionTypes";

export default (state = {}, action) => {
    switch (action.type) {
        case actionTypes.EVENTS_LOADING_SUCCESS:
            debugger;
            return {
                ...state,
                ...action.response.filters
            };
        case actionTypes.START_DATE_QUERY_CHANGED:
            debugger;
            return {
                ...state,
                startDate: action.startDate
            };
        case actionTypes.END_DATE_QUERY_CHANGED:
            debugger;
            return {
                ...state,
                endDate: action.endDate
            };
        default:
            return state;
    }
};