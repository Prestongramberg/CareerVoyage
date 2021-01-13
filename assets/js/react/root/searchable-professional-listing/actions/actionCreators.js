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

export function query() {

    debugger;
    return (dispatch, getState) => {

        debugger;
        const state = getState();

        const url = window.Routing.generate('get_professionals_by_radius');

        //const url = window.Routing.generate("global_share_data") + '?page=' + state.filters.current_page;

        return api.post(url, state)
            .then((response) => {

                debugger;
                if (response.statusCode < 300) {
                    dispatch({type: actionTypes.PROFESSIONALS_LOADING_SUCCESS, response: response.responseBody})
                }  else {
                    dispatch({
                        type: actionTypes.PROFESSIONALS_LOADING_FAILURE
                    })

                }
            })
            .catch((e)=> {
                dispatch({
                    type: actionTypes.PROFESSIONALS_LOADING_FAILURE
                })
            })
    }
}
