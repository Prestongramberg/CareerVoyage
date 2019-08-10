import * as actionTypes from "./actionTypes";
import * as api  from '../../../utilities/api/api'

export function primaryIndustryChanged(industryId) {
    return {
        type: actionTypes.PRIMARY_INDUSTRY_CHANGED,
        industryId: parseInt( industryId )
    };
}

export function loadIndustries(url) {
    return (dispatch, getState) => {
        dispatch({type: actionTypes.LOAD_INDUSTRIES})

        return api.get(url)
            .then((response) => {
                if (response.statusCode < 300) {
                    dispatch({type: actionTypes.LOAD_INDUSTRIES_SUCCESS, response: response.responseBody})
                }  else {
                    dispatch({
                        type: actionTypes.LOAD_INDUSTRIES_FAILURE,
                        error: "Something went wrong, please try refreshing the page."
                    })

                }
            })
            .catch(()=> dispatch({
                type: actionTypes.LOAD_INDUSTRIES_FAILURE,
                error: "Something went wrong, please try refreshing the page."
            }))
    }
}

export function subscribe(industryId) {
    return (dispatch, getState) => {
        dispatch({type: actionTypes.SUBSCRIBE})

        return api.get(url)
            .then((response) => {
                if (response.statusCode < 300) {
                    dispatch({type: actionTypes.SUBSCRIBE_SUCCESS, response: response.responseBody})
                }  else {
                    dispatch({
                        type: actionTypes.SUBSCRIBE_FAILURE,
                        error: "Something went wrong, please try refreshing the page."
                    })

                }
            })
            .catch(()=> dispatch({
                type: actionTypes.SUBSCRIBE_FAILURE,
                error: "Something went wrong, please try refreshing the page."
            }))
    }
}

export function unsubscribe(industryId) {
    return (dispatch, getState) => {
        dispatch({type: actionTypes.UNSUBSCRIBE})

        return api.get(url)
            .then((response) => {
                if (response.statusCode < 300) {
                    dispatch({type: actionTypes.UNSUBSCRIBE_SUCCESS, response: response.responseBody})
                }  else {
                    dispatch({
                        type: actionTypes.UNSUBSCRIBE_FAILURE,
                        error: "Something went wrong, please try refreshing the page."
                    })

                }
            })
            .catch(()=> dispatch({
                type: actionTypes.UNSUBSCRIBE_FAILURE,
                error: "Something went wrong, please try refreshing the page."
            }))
    }
}