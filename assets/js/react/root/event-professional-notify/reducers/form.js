import * as actionTypes from "../actions/actionTypes";

export default (state = {}, action) => {

    switch (action.type) {
        case actionTypes.SELECT_FIELD_CHANGED:
            return {
                ...state,
                professionals: action.professionals
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
