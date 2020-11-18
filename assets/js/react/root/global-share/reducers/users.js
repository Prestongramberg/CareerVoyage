import * as actionTypes from "../actions/actionTypes";

export default (state = {}, action) => {

    switch (action.type) {
        case actionTypes.SEARCH_CHATTABLE_USERS_SUCCESS:

            return {
                ...state,
                professionals: action.users.professionals,
                students: action.users.students,
                educators: action.users.educators,
                school_admins: action.users.school_admins,
                all: action.users.all
            };

            //return action.users.sort((a, b) => ( (a.first_name.toUpperCase() + a.last_name.toUpperCase()) > (b.first_name.toUpperCase() + b.last_name.toUpperCase())) ? 1 : -1);

        case actionTypes.NOTIFICATIONS_SENDING_SUCCESS:
            return state;

        default:
            return state;
    }
};
