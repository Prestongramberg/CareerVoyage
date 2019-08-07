import * as actionTypes from "../actions/actionTypes";

export default (state = {}, action) => {
    switch (action.type) {
        case actionTypes.USER_LOADING_SUCCESS:
            return action.response.data.lessonFavorites.map(lessonFavorite => lessonFavorite.lesson.id);
        case actionTypes.LESSON_FAVORITE:
            return state.indexOf(action.lessonId) === -1 ? state.concat([action.lessonId]) : state;
        case actionTypes.LESSON_UNFAVORITE:
            return state.filter(lessonId => lessonId !== action.lessonId);
        default:
            return state;
    }
};