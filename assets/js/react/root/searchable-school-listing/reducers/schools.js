import * as actionTypes from "../actions/actionTypes";
import {shuffle} from "../../../utilities/array-utils";

export default (state = {}, action) => {
    switch (action.type) {
        case actionTypes.SCHOOLS_LOADING_SUCCESS:
            return shuffle(action.response.data);
        default:
            return state;
    }
};
