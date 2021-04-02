import * as actionTypes from "../actions/actionTypes";

export default (state = {}, action) => {

    switch (action.type) {
        case actionTypes.SEARCH_SUCCESS:
            return {
                ...state,
                items: action.data.items,
                schema: action.data.schema,
                pagination: action.data.pagination,
                loading: false,
                notifiedUsers: action.data.notifiedUsers
            }
        case actionTypes.FILTER_CHANGE_SUCCESS:
        case actionTypes.PAGE_CHANGE_SUCCESS:
        case actionTypes.LOAD_INITIAL_DATA_SUCCESS:
            return {
                ...state,
                items: action.data.items,
                schema: action.data.schema,
                pagination: action.data.pagination,
                loading: false
            }
        case actionTypes.SEARCH_LOADING:
        case actionTypes.FILTER_CHANGE_REQUESTED:
            return {
                ...state,
                loading: true,
                typingTimeout: action.typingTimeout
            }
        case actionTypes.PAGE_CHANGE_REQUESTED:
        case actionTypes.LOAD_INITIAL_DATA_REQUESTED:
            return {
                ...state,
                loading: true,
            }
        case actionTypes.SEARCH_QUERY:


            return {
                ...state,
                form: {...state.form, [action.fieldName]: action.fieldValue}
            };

        case actionTypes.UPDATE_MESSAGE:

            return {
                ...state,
                user_modified_messages: {...state.user_modified_messages, [action.userId]: true},
                user_messages: {...state.user_messages, [action.userId]: action.message}
            };

        case actionTypes.NOTIFICATIONS_SENDING:


            return {
                ...state,
                currentNotifiedUser: action.userId
            };

        case actionTypes.NOTIFICATIONS_SENDING_SUCCESS:

            return {
                ...state,
                currentNotifiedUser: null,
                notifiedUsers: [...state.notifiedUsers, action.userId]
            };

        case actionTypes.NOTIFICATIONS_SENDING_FAILURE:

            return {
                ...state,
                currentNotifiedUser: null
            };

        default:
            return state;
    }
};
