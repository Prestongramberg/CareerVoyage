import * as actionTypes from "../actions/actionTypes";

export default (state = {}, action) => {
    switch (action.type) {
        case actionTypes.EDUCATORS_LOADING_SUCCESS:
            return get_schools_from_request( action.response.data );
        default:
            return state;
    }
};

function get_schools_from_request( educators ) {
    const school_ids = [];
    const schools = [];

    educators.forEach(educator => {
        if ( educator.school && educator.school.id && school_ids.indexOf(educator.school.id) === -1 ) {
            school_ids.push(educator.school.id);
            schools.push(educator.school);
        }
    });

    console.log(schools);

    return schools.sort((a, b) => (a.name > b.name) ? 1 : -1);
}
