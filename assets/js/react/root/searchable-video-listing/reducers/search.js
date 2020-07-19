import * as actionTypes from "../actions/actionTypes";

export default (state = {}, action) => {
    switch (action.type) {
        case actionTypes.USER_LOADING_SUCCESS:
        case actionTypes.USER_LOADING_FAILURE:
            return {
                ...state,
                loadingUser: false
            };
        case actionTypes.VIDEOS_LOADING:
            return {
                ...state,
                loading: true
            };
        case actionTypes.VIDEOS_LOADING_SUCCESS:
        case actionTypes.VIDEOS_LOADING_FAILURE:
            return {
                ...state,
                loadingVideos: false,
                loading: false
            };
        default:
            return state;
    }
};
