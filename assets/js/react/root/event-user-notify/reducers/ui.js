import * as actionTypes from "../actions/actionTypes";

export default (state = {}, action) => {
    switch (action.type) {
        case actionTypes.USERS_LOADING :
            return {
                ...state,
                loading: true
            };
        case actionTypes.USERS_LOADING_SUCCESS:
            return {
                ...state,
                loading: false
            };
        case actionTypes.NOTIFY_BUTTON_CLICKED:
            return {
                ...state,
                showModal: true
            };
        case actionTypes.CLOSE_BUTTON_CLICKED:
            return {
                ...state,
                showModal: false
            };
        case actionTypes.NOTIFICATIONS_SENT:
            return {
                ...state,
                showModal: false
            };
        default:
            return state;
    }
};
