import * as actionTypes from "../actions/actionTypes";

export default (state = {}, action) => {

    switch (action.type) {

        case actionTypes.SEARCH_CHATTABLE_USERS_SUCCESS:
            return action.users.sort((a, b) => ( (a.first_name.toUpperCase() + a.last_name.toUpperCase()) > (b.first_name.toUpperCase() + b.last_name.toUpperCase())) ? 1 : -1);

        case actionTypes.NOTIFICATIONS_SENDING_SUCCESS:
            return []

        default:
            return state;
    }
};
