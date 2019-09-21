import * as actionTypes from "./actionTypes";
import * as api  from '../../../utilities/api/api'

export function openChat() {
    return {
        type: actionTypes.OPEN_CHAT
    }
}

export function loadChatHistory() {
    return (dispatch, getState) => {
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
