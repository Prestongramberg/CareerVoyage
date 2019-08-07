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

export function loadUser(url) {
    return (dispatch, getState) => {
        dispatch({type: actionTypes.USER_LOADING})

        return api.get(url)
            .then((response) => {
                if (response.statusCode < 300) {
                    dispatch({type: actionTypes.USER_LOADING_SUCCESS, response: response.responseBody})
                }  else {
                    dispatch({
                        type: actionTypes.USER_LOADING_FAILURE
                    })

                }
            })
            .catch(()=> dispatch({
                type: actionTypes.USER_LOADING_SUCCESS
            }))
    }
}

export function companyFavorite(companyId) {

    const url = window.Routing.generate("favorite_company", {id: companyId});

    return (dispatch, getState) => {
        dispatch({type: actionTypes.COMPANY_FAVORITE})

        return api.post(url)
            .then((response) => {
                if (response.statusCode < 300 && response.responseBody.success === true) {
                    dispatch({type: actionTypes.COMPANY_FAVORITE_SUCCESS, companyId: companyId })
                }  else {
                    dispatch({
                        type: actionTypes.COMPANY_FAVORITE_FAILURE
                    })

                }
            })
            .catch(()=> dispatch({
                type: actionTypes.COMPANY_FAVORITE_FAILURE
            }))
    }
}

export function companyUnfavorite(companyId) {

    const url = window.Routing.generate("unfavorite_company", {id: companyId});

    return (dispatch, getState) => {
        dispatch({type: actionTypes.COMPANY_UNFAVORITE})

        return api.post(url)
            .then((response) => {
                console.log(response);
                if (response.statusCode < 300 && response.responseBody.success === true) {
                    dispatch({type: actionTypes.COMPANY_UNFAVORITE_SUCCESS, companyId: companyId})
                }  else {
                    dispatch({
                        type: actionTypes.COMPANY_UNFAVORITE_FAILURE
                    })

                }
            })
            .catch(()=> dispatch({
                type: actionTypes.COMPANY_UNFAVORITE_FAILURE
            }))
    }
}
