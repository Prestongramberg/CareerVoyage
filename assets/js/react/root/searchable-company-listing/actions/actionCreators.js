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

export function radiusChanged(radius) {
    return {
        type: actionTypes.RADIUS_CHANGED,
        radius: radius
    };
}

export function zipcodeChanged(zipcode) {
    return {
        type: actionTypes.ZIPCODE_CHANGED,
        zipcode: zipcode
    };
}

export function companyFavorited(companyId) {
    return {
        type: actionTypes.COMPANY_FAVORITE,
        companyId: companyId
    };
}

export function companyUnfavorited(companyId) {
    return {
        type: actionTypes.COMPANY_UNFAVORITE,
        companyId: companyId
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
                type: actionTypes.USER_LOADING_FAILURE
            }))
    }
}
