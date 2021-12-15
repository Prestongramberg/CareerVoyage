import * as actionTypes from "./actionTypes";
import * as api from '../../../utilities/api/api'
import {START_DATE_CHANGED} from "./actionTypes";

export function updateSearchQuery(query) {
    return {
        type: actionTypes.SEARCH_QUERY_CHANGED,
        searchQuery: query
    };
}

export function eventsRefreshed() {
    return {
        type: actionTypes.EVENTS_REFRESHED,
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

export function setEvents(events) {
    debugger;
    return {
        type: actionTypes.EVENTS_LOADING_SUCCESS,
        response: events
    };
}

export function updateCompanyQuery(company) {
    return {
        type: actionTypes.COMPANY_QUERY_CHANGED,
        company: company
    };
}

export function updatePrimaryIndustryQuery(industry) {
    debugger;
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

export function startDateChanged(date) {
    return {
        type: actionTypes.START_DATE_CHANGED,
        date: date
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
                } else {
                    dispatch({
                        type: actionTypes.EVENTS_LOADING_FAILURE
                    })

                }
            })
            .catch(() => dispatch({
                type: actionTypes.EVENTS_LOADING_FAILURE
            }))
    }
}


