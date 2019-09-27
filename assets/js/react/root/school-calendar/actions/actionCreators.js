import * as actionTypes from "./actionTypes";
import * as api  from '../../../utilities/api/api'

export function loadEvents(url) {
    return (dispatch, getState) => {
        dispatch({type: actionTypes.EVENTS_LOADING})

        return api.get(url)
            .then((response) => {
                if (response.statusCode < 300) {
                    dispatch({type: actionTypes.EVENTS_LOADING_SUCCESS, response: response.responseBody})
                }  else {
                    dispatch({
                        type: actionTypes.EVENTS_LOADING_FAILURE
                    })
                }
            })
            .catch(()=> dispatch({
                type: actionTypes.EVENTS_LOADING_FAILURE
            }))
    }
}
