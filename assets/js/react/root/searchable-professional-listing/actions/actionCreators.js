import * as actionTypes from "./actionTypes";
import * as api  from '../../../utilities/api/api'

export function updateSearchQuery(query) {
    return {
        type: actionTypes.SEARCH_QUERY_CHANGED,
        query: query
    };
}

export function updateCompanyQuery(company) {
    return {
        type: actionTypes.COMPANY_QUERY_CHANGED,
        company: company
    };
}

export function updateRoleQuery(role) {
    return {
        type: actionTypes.ROLE_QUERY_CHANGED,
        role: role
    };
}

export function updatePrimaryIndustryQuery(industry) {
    return {
        type: actionTypes.PRIMARY_INDUSTRY_QUERY_CHANGED,
        industry: industry
    };
}

export function updateSecondaryIndustryQuery(industry) {
    return {
        type: actionTypes.SECONDARY_INDUSTRY_QUERY_CHANGED,
        industry: industry
    };
}

export function loadProfessionals(url) {
    return (dispatch, getState) => {
        dispatch({type: actionTypes.PROFESSIONALS_LOADING})

        return api.get(url)
            .then((response) => {
                if (response.statusCode < 300) {
                    dispatch({type: actionTypes.PROFESSIONALS_LOADING_SUCCESS, response: response.responseBody})
                }  else {
                    dispatch({
                        type: actionTypes.PROFESSIONALS_LOADING_FAILURE
                    })

                }
            })
            .catch(()=> dispatch({
                type: actionTypes.PROFESSIONALS_LOADING_FAILURE
            }))
    }
}