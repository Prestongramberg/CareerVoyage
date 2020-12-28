import * as actionTypes from "./actionTypes";
import * as api  from '../../../utilities/api/api'

export function updateSearchQuery(query) {
    return {
        type: actionTypes.SEARCH_QUERY_CHANGED,
        query: query
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

export function updateCompanyQuery(company) {
    return {
        type: actionTypes.COMPANY_QUERY_CHANGED,
        company: company
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

export function updateEventTypeQuery(eventType) {
    return {
        type: actionTypes.EVENT_TYPE_QUERY_CHANGED,
        eventType: eventType
    };
}

export function loadEvents(url) {
    return (dispatch, getState) => {
        dispatch({type: actionTypes.EVENTS_LOADING})

        return api.get(url)
            .then((response) => {
                if (response.statusCode < 300) {
                    debugger;
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
