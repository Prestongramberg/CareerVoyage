import * as actionTypes from "../actions/actionTypes";

export default (state = {}, action) => {

    switch (action.type) {

        case actionTypes.SEARCH_CHATTABLE_USERS_SUCCESS:
            return action.users;

        case actionTypes.NOTIFICATIONS_SENDING_SUCCESS:
            return []

        default:
            return state;
    }
};
