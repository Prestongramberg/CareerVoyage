import * as actionTypes from "./actionTypes";
import * as api  from '../../../utilities/api/api'

export function updateSearchQuery(query) {
    return {
        type: actionTypes.SEARCH_QUERY_CHANGED,
        query: query
    };
}

export function updateCourseQuery(course) {
    return {
        type: actionTypes.COURSE_QUERY_CHANGED,
        course: course
    };
}

export function loadLessons(url) {
    return (dispatch, getState) => {
        dispatch({type: actionTypes.LESSONS_LOADING})

        return api.get(url)
            .then((response) => {
                if (response.statusCode < 300) {
                    dispatch({type: actionTypes.LESSONS_LOADING_SUCCESS, response: response.responseBody})
                }  else {
                    dispatch({
                        type: actionTypes.LESSONS_LOADING_FAILURE,
                        error: "Something went wrong, please try refreshing the page."
                    })

                }
            })
            .catch(()=> dispatch({
                type: actionTypes.LESSONS_LOADING_FAILURE,
                error: "Something went wrong, please try refreshing the page."
            }))
    }
}

export function loadCourses(url) {
    return (dispatch, getState) => {
        dispatch({type: actionTypes.COURSES_LOADING})

        return api.get(url)
            .then((response) => {
                if (response.statusCode < 300) {
                    dispatch({type: actionTypes.COURSES_LOADING_SUCCESS, response: response.responseBody})
                }  else {
                    dispatch({
                        type: actionTypes.COURSES_LOADING_FAILURE
                    })

                }
            })
            .catch(()=> dispatch({
                type: actionTypes.COURSES_LOADING_FAILURE
            }))
    }
}

export function lessonFavorite(lessonId) {

    const url = window.Routing.generate("favorite_lesson", {id: lessonId});

    return (dispatch, getState) => {
        dispatch({type: actionTypes.LESSON_FAVORITE})

        return api.post(url)
            .then((response) => {
                if (response.statusCode < 300 && response.responseBody.success === true) {
                    dispatch({type: actionTypes.LESSON_FAVORITE_SUCCESS, lessonId: lessonId })
                }  else {
                    dispatch({
                        type: actionTypes.LESSON_FAVORITE_FAILURE
                    })

                }
            })
            .catch(()=> dispatch({
                type: actionTypes.LESSON_FAVORITE_FAILURE
            }))
    }
}

export function lessonUnfavorite(lessonId) {

    const url = window.Routing.generate("unfavorite_lesson", {id: lessonId});

    return (dispatch, getState) => {
        dispatch({type: actionTypes.LESSON_UNFAVORITE})

        return api.post(url)
            .then((response) => {
                console.log(response);
                if (response.statusCode < 300 && response.responseBody.success === true) {
                    dispatch({type: actionTypes.LESSON_UNFAVORITE_SUCCESS, lessonId: lessonId})
                }  else {
                    dispatch({
                        type: actionTypes.LESSON_UNFAVORITE_FAILURE
                    })

                }
            })
            .catch(()=> dispatch({
                type: actionTypes.LESSON_UNFAVORITE_FAILURE
            }))
    }
}

export function lessonTeach(lessonId) {

    const url = window.Routing.generate("teach_lesson", {id: lessonId});

    console.log(url);

    return (dispatch, getState) => {
        dispatch({type: actionTypes.LESSON_TEACH})

        return api.post(url)
            .then((response) => {
                if (response.statusCode < 300 && response.responseBody.success === true) {
                    dispatch({type: actionTypes.LESSON_TEACH_SUCCESS, lessonId: lessonId })
                }  else {
                    dispatch({
                        type: actionTypes.LESSON_TEACH_FAILURE
                    })

                }
            })
            .catch(()=> dispatch({
                type: actionTypes.LESSON_TEACH_FAILURE
            }))
    }
}

export function lessonUnteach(lessonId) {

    const url = window.Routing.generate("unteach_lesson", {id: lessonId});

    console.log(url);

    return (dispatch, getState) => {
        dispatch({type: actionTypes.LESSON_UNTEACH})

        return api.post(url)
            .then((response) => {
                if (response.statusCode < 300 && response.responseBody.success === true) {
                    dispatch({type: actionTypes.LESSON_UNTEACH_SUCCESS, lessonId: lessonId })
                }  else {
                    dispatch({
                        type: actionTypes.LESSON_UNTEACH_FAILURE
                    })

                }
            })
            .catch(()=> dispatch({
                type: actionTypes.LESSON_UNTEACH_FAILURE
            }))
    }
}
