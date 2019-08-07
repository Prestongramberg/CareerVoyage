import * as actionTypes from "../actions/actionTypes";

export default (state = {}, action) => {
    switch (action.type) {
        case actionTypes.USER_LOADING_SUCCESS:
            return action.response.data;
        default:
            return state;
    }
};