import * as actionTypes from "../actions/actionTypes";

export default (state = {}, action) => {
    switch (action.type) {
        case actionTypes.USER_LOADING_SUCCESS:
            return action.response.data.lessonTeachables.map(lessonTeachable => lessonTeachable.lesson.id);
        case actionTypes.LESSON_TEACH:
            return state.indexOf(action.lessonId) === -1 ? state.concat([action.lessonId]) : state;
        case actionTypes.LESSON_UNTEACH:
            return state.filter(lessonId => lessonId !== action.lessonId);
        default:
            return state;
    }
};