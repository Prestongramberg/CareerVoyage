import * as actionTypes from "../actions/actionTypes";

export default (state = {}, action) => {

    switch (action.type) {
        case actionTypes.SELECT_FIELD_CHANGED:
            return {
                ...state,
                users: action.users
            };
        case actionTypes.TEXTAREA_FIELD_CHANGED:
            return {
                ...state,
                customMessage: action.customMessage
            };
        default:
            return state;
    }
};
