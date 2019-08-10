import * as actionTypes from "./actionTypes";
import * as api  from '../../../utilities/api/api'

export function sampleActionCreator(query) {
    return {
        type: actionTypes.SAMPLE_ACTION,
        query: query
    };
}

export function sampleAsyncActionCreator(url) {
    return (dispatch, getState) => {
        dispatch({type: actionTypes.SAMPLE_REQUEST})

        return api.get(url)
            .then((response) => {
                if (response.statusCode < 300) {
                    dispatch({type: actionTypes.SAMPLE_REQUEST_SUCCESS, response: response.responseBody})
                }  else {
                    dispatch({
                        type: actionTypes.SAMPLE_REQUEST_FAILURE,
                        error: "Something went wrong, please try refreshing the page."
                    })

                }
            })
            .catch(()=> dispatch({
                type: actionTypes.SAMPLE_REQUEST_SUCCESS,
                error: "Something went wrong, please try refreshing the page."
            }))
    }
}