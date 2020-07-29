import * as actionTypes from "../actions/actionTypes";
// import {shuffle} from "../../../utilities/array-utils";

export default (state = {}, action) => {
    switch (action.type) {
        case actionTypes.EDUCATORS_LOADING_SUCCESS:
            return get_courses_from_request( action.response.data );
        default:
            return state;
    }
};

function get_courses_from_request( educators ) {

    const course_ids = [];
    const courses = [];

    educators.forEach(educator => {
        if ( educator.myCourses.length > 0) {
            educator.myCourses.forEach(course => {
                course_ids.push(course.id);
                courses.push(course);
            })
        }
    });

    console.log(educators);

    return courses.sort((a, b) => (a.title > b.title) ? 1 : -1);

}
