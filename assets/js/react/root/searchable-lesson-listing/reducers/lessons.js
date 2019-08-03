import * as actionTypes from "../actions/actionTypes";

export default (state = {}, action) => {
    switch (action.type) {
        case actionTypes.LESSONS_LOADING_SUCCESS:
            return action.response.data;
        case actionTypes.LESSON_FAVORITE:
        case actionTypes.LESSON_UNFAVORITE:
            return state.map((item, index) => {
                if (item.id !== action.lessonId) {
                    return item
                }
                return {
                    ...item,
                    favorite: !item.favorite
                }
            });
        case actionTypes.LESSON_TEACH_SUCCESS:
        case actionTypes.LESSON_UNTEACH_SUCCESS:
            return state.map((item, index) => {
                if (item.id !== action.lessonId) {
                    return item
                }
                return {
                    ...item,
                    teachable: !item.teachable
                }
            });
        default:
            return state;
    }
};
