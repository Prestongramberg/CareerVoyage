import * as actionTypes from "../actions/actionTypes";

export default (state = {}, action) => {
    switch (action.type) {
        case actionTypes.EVENTS_LOADING_SUCCESS:
            return {
                ...state,
                ...action.response.filters
            };
        case actionTypes.START_DATE_CHANGED:
            return {
                ...state,
                startDate: action.date
            };
        default:
            return state;
    }
};