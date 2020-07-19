import * as actionTypes from "./actionTypes";
import * as api  from '../../../utilities/api/api'

export function primaryIndustryChanged(industryId) {
    return {
        type: actionTypes.PRIMARY_INDUSTRY_CHANGED,
        industryId: parseInt( industryId )
    };
}

export function subscribe(industryId) {
    return {
        type: actionTypes.SUBSCRIBE,
        industryId: parseInt( industryId )
    };
}

export function unsubscribe(industryId) {
    return {
        type: actionTypes.UNSUBSCRIBE,
        industryId: parseInt( industryId )
    };
}

export function unsubscribeAll() {
    return {
        type: actionTypes.UNSUBSCRIBE_ALL
    }
}

export function loadIndustries(url, removeDomId) {
    return (dispatch, getState) => {
        dispatch({type: actionTypes.LOAD_INDUSTRIES})

        return api.get(url)
            .then((response) => {
                if (response.statusCode < 300) {
                    dispatch({type: actionTypes.LOAD_INDUSTRIES_SUCCESS, response: response.responseBody});
                    $(`#${removeDomId}`).remove();
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