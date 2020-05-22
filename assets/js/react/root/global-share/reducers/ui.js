import * as actionTypes from "../actions/actionTypes";

const initialState = {
    query: '',
    users: []
}

export default (state = { ...initialState }, action) => {

    switch (action.type) {
        case actionTypes.SEARCH_CHATTABLE_USERS:
            return {
                ...state,
                query: action.searchQuery
            }

        case actionTypes.ADD_USER:
            return {
                ...state,
                users: state.users.filter(user => user.id !== action.user.id).concat(action.user)
            }

        case actionTypes.REMOVE_USER:
            return {
                ...state,
                users: state.users.filter(user => user.id !== action.user.id)
            }

        case actionTypes.NOTIFICATIONS_SENDING_SUCCESS:
            return {
                ...state,
                query: '',
                users: []
            }

        default:
            return state;
    }
};
