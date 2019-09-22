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
                isChatOpen: false
            }

        case actionTypes.TOGGLE_CHAT:
            return {
                ...state,
                isChatOpen: !state.isChatOpen
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

        default:
            return state;
    }
};
