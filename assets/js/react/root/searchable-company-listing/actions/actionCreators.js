import * as actionTypes from "./actionTypes";
import * as api  from '../../../utilities/api/api'

export function updateSearchQuery(query) {
    return {
        type: actionTypes.SEARCH_QUERY_CHANGED,
        query: query
    };
}

export function updateIndustryQuery(industry) {
    return {
        type: actionTypes.INDUSTRY_QUERY_CHANGED,
        industry: industry
    };
}

export function loadCompanies(url) {
    return (dispatch, getState) => {
        dispatch({type: actionTypes.COMPANIES_LOADING})

        return api.get(url)
            .then((response) => {
                if (response.statusCode < 300) {
                    dispatch({type: actionTypes.COMPANIES_LOADING_SUCCESS, response: response.responseBody})
                }  else {
                    dispatch({
                        type: actionTypes.COMPANIES_LOADING_FAILURE,
                        error: "Something went wrong, please try refreshing the page."
                    })

                }
            })
            .catch(()=> dispatch({
                type: actionTypes.COMPANIES_LOADING_FAILURE,
                error: "Something went wrong, please try refreshing the page."
            }))
    }
}

export function loadIndustries(url) {
    return (dispatch, getState) => {
        dispatch({type: actionTypes.INDUSTRIES_LOADING})

        return api.get(url)
            .then((response) => {
                if (response.statusCode < 300) {
                    dispatch({type: actionTypes.INDUSTRIES_LOADING_SUCCESS, response: response.responseBody})
                }  else {
                    dispatch({
                        type: actionTypes.INDUSTRIES_LOADING_FAILURE
                    })

                }
            })
            .catch(()=> dispatch({
                type: actionTypes.INDUSTRIES_LOADING_FAILURE
            }))
    }
}
