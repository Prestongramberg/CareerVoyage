import * as actionTypes from "../actions/actionTypes";

export default (state = {}, action) => {
    switch (action.type) {
        case actionTypes.EVENTS_LOADING_SUCCESS:
            debugger;
            return action.response.filters;
        default:
            return state;
    }
};