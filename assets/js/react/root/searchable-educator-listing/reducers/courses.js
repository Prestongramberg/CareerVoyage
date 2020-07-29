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

    console.log("Did it enter here?")
    const course_ids = [];
    const courses = [];

    // educators.forEach(educator => {
    //     if ( educator.lesson.course && educator.lesson.course.id && course_ids.indexOf(educator.lesson.course.id) === -1 ) {
    //         course_ids.push(educator.lesson.course.id);
    //         courses.push(educator.lesson.course);
    //     }
    // });
    course_ids[1, 2]
    courses.push({ id: 1, title: "Something"}, { id: 2, title: "Another"})

    console.log(courses);

    return courses;

}
