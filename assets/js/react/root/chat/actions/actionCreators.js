import * as actionTypes from "./actionTypes";
import * as api  from '../../../utilities/api/api'

export function toggleChat() {
    return {
        type: actionTypes.TOGGLE_CHAT
    }
}

export function showHistory() {
    return {
        type: actionTypes.SHOW_HISTORY
    }
}

export function showSearch() {
    return {
        type: actionTypes.SHOW_SEARCH
    }
}

export function openChat() {
    return {
        type: actionTypes.OPEN_CHAT
    }
}

export function loadChatHistory() {
    return (dispatch, getState) => {

        const url = window.Routing.generate("get_chat_history", { id: window.SETTINGS.LOGGED_IN_USER_ID })

        dispatch({type: actionTypes.LOADING_CHAT_HISTORY})

        return api.get(url)
            .then((response) => {
                if (response.statusCode < 300) {
                    dispatch({type: actionTypes.LOADING_CHAT_HISTORY_SUCCESS, response: response.responseBody})
                }  else {
                    dispatch({
                        type: actionTypes.LOADING_CHAT_HISTORY_FAILURE
                    })

                }
            })
            .catch(()=> dispatch({
                type: actionTypes.LOADING_CHAT_HISTORY_FAILURE
            }))
    }
}
