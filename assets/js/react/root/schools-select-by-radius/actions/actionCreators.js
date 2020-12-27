import * as actionTypes from "./actionTypes";
import * as api  from '../../../utilities/api/api'

export function radiusChanged(radius) {
    return {
        type: actionTypes.RADIUS_CHANGED,
        radius: radius
    };
}

export function schoolToggled(schoolId) {
    return {
        type: actionTypes.SCHOOL_TOGGLED,
        schoolId: schoolId
    };
}

export function zipcodeChanged(zipcode) {
    return {
        type: actionTypes.ZIPCODE_CHANGED,
        zipcode: zipcode
    };
}

export function selectAll() {
    return (dispatch, getState) => {

        const { schools } = getState();
        const schoolIds = schools.map(school => school.id);

        dispatch({ type: actionTypes.SELECT_ALL, schoolIds: schoolIds })
    }
}

export function unSelectAll() {
    return {
        type: actionTypes.UNSELECT_ALL
    };
}

export function loadSchools(url) {
    debugger;
    return (dispatch, getState) => {

        const { search }  = getState();

        dispatch({type: actionTypes.SCHOOLS_LOADING})

        return api.get(url)
            .then((response) => {
                if (response.statusCode < 300) {
                    dispatch({type: actionTypes.SCHOOLS_LOADING_SUCCESS, response: response.responseBody})
                }  else {
                    dispatch({
                        type: actionTypes.SCHOOLS_LOADING_FAILURE,
                        error: "Something went wrong, please try refreshing the page."
                    })

                }
            })
            .catch(()=> dispatch({
                type: actionTypes.SCHOOLS_LOADING_FAILURE,
                error: "Something went wrong, please try refreshing the page."
            }))
    }
}
