import * as actionTypes from "../actions/actionTypes";

export default (state = {}, action) => {
    switch (action.type) {
        case actionTypes.LESSONS_LOADING_SUCCESS:
            return get_courses_from_request( action.response.data );
        default:
            return state;
    }
};

function get_courses_from_request( lessons ) {
    const course_ids = [];
    const courses = [];

    lessons.forEach(lesson => {
        if ( lesson.primaryCourse && lesson.primaryCourse.id && course_ids.indexOf(lesson.primaryCourse.id) === -1  ) {
            course_ids.push(lesson.primaryCourse.id);
            courses.push(lesson.primaryCourse);
        }
        lesson.secondaryCourses.forEach(course => {
            if ( course_ids.indexOf(course.id) === -1  ) {
                course_ids.push(course.id);
                courses.push(course);
            }
        })
    });

    return courses.sort((a, b) => (a.title > b.title) ? 1 : -1);
}
