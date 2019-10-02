import * as actionTypes from "../actions/actionTypes";

export default (state = {}, action) => {
    switch (action.type) {

        case actionTypes.CLOSE_CHAT:
            return {
                ...state,
                chatId: 0,
                messages: [],
                usersHistory: []
            }

        case actionTypes.LOADING_CHAT_HISTORY:
            return {
                ...state,
                usersHistory: []
            }

        case actionTypes.LOADING_CHAT_HISTORY_SUCCESS:
            return {
                ...state,
                unreadMessages: action.unreadMessages,
                usersHistory: action.usersHistory
            }

        case actionTypes.CLOSE_THREAD:
            return {
                ...state,
                chatId: 0,
                messages: []
            }

        case actionTypes.LOADING_THREAD_SUCCESS:
            return {
                ...state,
                chatId: action.chatId,
                messages: action.messages,
                userEngagedWith: action.userEngagedWith
            }

        case actionTypes.UPDATE_MESSAGE:
            return {
                ...state,
                currentMessage: action.message
            }

        case actionTypes.SEND_MESSAGE_SUCCESS:
            return {
                ...state,
                currentMessage: ""
            }

        case actionTypes.INCREMENT_LIVE_CHAT:
            return {
                ...state,
                unreadMessages: state.unreadMessages + 1
            }

        case actionTypes.UPDATE_SEARCH:
            return {
                ...state,
                searchTerm: action.search
            }

        case actionTypes.SEARCH_SUCCESS:
            return {
                ...state,
                foundUsersInSearch: action.users
            }

        case actionTypes.POPULATE_MESSAGE:
            return {
                ...state,
                currentMessage: action.message
            }

        default:
            return state;
    }
};
