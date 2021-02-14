import * as actionTypes from "../actions/actionTypes";
import {shuffle} from "../../../utilities/array-utils";

export default (state = {}, action) => {
    switch (action.type) {
        case actionTypes.VIDEOS_LOADING_SUCCESS:
            return shuffle(action.response.data.allVideos);
        case actionTypes.VIDEO_FAVORITE:
        case actionTypes.VIDEO_UNFAVORITE:
            return state.map((item, index) => {
                if (item.id !== action.videoId) {
                    return item
                }
                return {
                    ...item,
                    favorite: !item.favorite
                }
            });
        default:
            return state;
    }
};
