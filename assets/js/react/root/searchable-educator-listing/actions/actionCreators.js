import * as actionTypes from "./actionTypes";
import * as api  from '../../../utilities/api/api'

export function updateSearchQuery(query) {
    return {
        type: actionTypes.SEARCH_QUERY_CHANGED,
        query: query
    };
}

export function updateSchoolQuery(school) {
    return {
        type: actionTypes.SCHOOL_QUERY_CHANGED,
        school: school
    };
}

export function updateCourseQuery(course) {
    return {
        type: actionTypes.COURSE_QUERY_CHANGED,
        course: course
    }
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

export function loadEducators(url) {
    return (dispatch, getState) => {

        dispatch({type: actionTypes.EDUCATORS_LOADING})

        return api.get(url)
            .then((response) => {
                if (response.statusCode < 300) {
                    dispatch({type: actionTypes.EDUCATORS_LOADING_SUCCESS, response: response.responseBody})
                }  else {
                    dispatch({
                        type: actionTypes.EDUCATORS_LOADING_FAILURE
                    })

                }
            })
            .catch(()=> dispatch({
                type: actionTypes.EDUCATORS_LOADING_FAILURE
            }))
    }
}
