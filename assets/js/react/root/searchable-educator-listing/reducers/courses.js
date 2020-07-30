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
        if(educator.myCourses) {
            educator.myCourses.forEach(course => {
                if(course_ids.indexOf(course.id) === -1){
                    course_ids.push( course.id )
                    courses.push(course);
                }
            })
        }
    });

    console.log(educators);
    console.log(courses);

    return courses.sort((a, b) => (a.title.toLowerCase() > b.title.toLowerCase()) ? 1 : -1);

}
