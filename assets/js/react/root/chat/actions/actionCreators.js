import * as actionTypes from "./actionTypes";
import * as api  from '../../../utilities/api/api'

export function loadChat(url) {
    return (dispatch, getState) => {
        dispatch({type: actionTypes.CHAT_LOAD})

        return api.get(url)
            .then((response) => {
                if (response.statusCode < 300) {
                    dispatch({type: actionTypes.CHAT_LOAD_SUCCESS, response: response.responseBody})
                }  else {
                    dispatch({
                        type: actionTypes.CHAT_LOAD_FAILURE
                    })

                }
            })
            .catch(()=> dispatch({
                type: actionTypes.CHAT_LOAD_FAILURE
            }))
    }
}
