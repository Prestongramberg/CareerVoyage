import * as actionTypes from "../actions/actionTypes";

export default (state = {}, action) => {
    switch (action.type) {
        case actionTypes.EVENTS_LOADING_SUCCESS:
        case actionTypes.EVENTS_LOADING_FAILURE:
            return {
                ...state,
                loading: !state.loading,
            };
        default:
            return state;
    }
};