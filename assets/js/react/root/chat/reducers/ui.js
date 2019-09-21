import * as actionTypes from "../actions/actionTypes";

export default (state = {}, action) => {
    switch (action.type) {

        case actionTypes.OPEN_CHAT:
            return {
                ...state,
                chatOpen: true
            }

        default:
            return state;
    }
};
