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

export function lessonFavorited(lessonId) {
    return {
        type: actionTypes.LESSON_FAVORITE,
        lessonId: lessonId
    };
}

export function lessonUnfavorited(lessonId) {
    return {
        type: actionTypes.LESSON_UNFAVORITE,
        lessonId: lessonId
    };
}

export function lessonTeach(lessonId) {
    return {
        type: actionTypes.LESSON_TEACH,
        lessonId: lessonId
    };
}

export function lessonUnteach(lessonId) {
    return {
        type: actionTypes.LESSON_UNTEACH,
        lessonId: lessonId
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

export function loadUser(url) {
    return (dispatch, getState) => {
        dispatch({type: actionTypes.USER_LOADING})

        return api.get(url)
            .then((response) => {
                if (response.statusCode < 300) {
                    dispatch({type: actionTypes.USER_LOADING_SUCCESS, response: response.responseBody})
                }  else {
                    dispatch({
                        type: actionTypes.USER_LOADING_FAILURE
                    })

                }
            })
            .catch(()=> dispatch({
                type: actionTypes.USER_LOADING_FAILURE
            }))
    }
}

export function updateExpertPresenterQuery(presenter) {
    return {
        type: actionTypes.EXPERT_PRESENTER_CHANGED,
        presenter: presenter
    };
}

export function updateEducatorRequestedQuery(educator) {
    return {
        type: actionTypes.EDUCATOR_REQUESTED_CHANGED,
        educator: educator
    };
}
