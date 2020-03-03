import * as actionTypes from "../actions/actionTypes";

export default (state = {}, action) => {
    switch (action.type) {
        case actionTypes.SCHOOLS_LOADING:
            return {
                ...state,
                loading: true
            };
        case actionTypes.SCHOOLS_LOADING_SUCCESS:
        case actionTypes.SCHOOLS_LOADING_FAILURE:
            return {
                ...state,
                loading: false
            };
        case actionTypes.SELECT_ALL:
            return {
                ...state,
                schoolStatus: selectAll( action.schoolIds )
            }
        case actionTypes.UNSELECT_ALL:
            return {
                ...state,
                schoolStatus: {}
            };
        case actionTypes.SCHOOL_TOGGLED:
            return {
                ...state,
                schoolStatus: {
                    ...state.schoolStatus,
                    [ 's' + action.schoolId ] : !!!state.schoolStatus[ [ 's' + action.schoolId ] ]
                }
            };
        case actionTypes.RADIUS_CHANGED:
            return {
                ...state,
                geoRadiusValue: action.radius
            };
        case actionTypes.ZIPCODE_CHANGED:
            return {
                ...state,
                geoZipCodeValue: action.zipcode
            };
        default:
            return state;
    }
};

function selectAll( schoolIds ) {
    let schoolsSelected = {};

    for( let i = 0; i < schoolIds.length; i++ ) {
        schoolsSelected[ `s${ schoolIds[ i ] }` ] = true;
    }

    return schoolsSelected;
}
