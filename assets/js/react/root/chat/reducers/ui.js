import * as actionTypes from "../actions/actionTypes";

export default (state = {}, action) => {
    switch (action.type) {

        case actionTypes.OPEN_CHAT:
            return {
                ...state,
                isChatOpen: true
            }

        case actionTypes.CLOSE_CHAT:
            return {
                ...state,
                isChatLoading: false,
                isChatOpen: false,
                isThreadLoading: false,
                isThreadOpen: false,
                isSearching: false
            }

        case actionTypes.SHOW_HISTORY:
            return {
                ...state,
                isSearchOpen: false
            }

        case actionTypes.SHOW_SEARCH:
            return {
                ...state,
                isSearchOpen: true
            }

        case actionTypes.LOADING_CHAT_HISTORY:
            return {
                ...state,
                isChatLoading: true
            }

        case actionTypes.LOADING_CHAT_HISTORY_SUCCESS:
        case actionTypes.LOADING_CHAT_HISTORY_FAILURE:
            return {
                ...state,
                isChatLoading: false
            }

        case actionTypes.OPEN_THREAD:
            return {
                ...state,
                isThreadOpen: true
            }

        case actionTypes.CLOSE_THREAD:
            return {
                ...state,
                isThreadOpen: false
            }

        case actionTypes.LOADING_THREAD:
            return {
                ...state,
                isThreadLoading: true
            }

        case actionTypes.LOADING_THREAD_SUCCESS:
        case actionTypes.LOADING_THREAD_FAILURE:
            return {
                ...state,
                isThreadLoading: false
            }

        case actionTypes.SEND_MESSAGE:
            return {
                ...state,
                isMessageSending: true
            }

        case actionTypes.SEND_MESSAGE_SUCCESS:
        case actionTypes.SEND_MESSAGE_FAILURE:
            return {
                ...state,
                isMessageSending: false
            }

        case actionTypes.SEARCH:
            return {
                ...state,
                isSearching: true
            }

        case actionTypes.SEARCH_SUCCESS:
        case actionTypes.SEARCH_FAILURE:
            return {
                ...state,
                isSearching: false
            }

        default:
            return state;
    }
};
