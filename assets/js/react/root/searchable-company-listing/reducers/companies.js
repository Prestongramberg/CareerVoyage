import * as actionTypes from "../actions/actionTypes";
import {shuffle} from "../../../utilities/array-utils";

export default (state = {}, action) => {
    switch (action.type) {
        case actionTypes.COMPANIES_LOADING_SUCCESS:
            return shuffle(action.response.data);
        case actionTypes.COMPANY_FAVORITE:
        case actionTypes.COMPANY_UNFAVORITE:
            return state.map((item, index) => {
                if (item.id !== action.companyId) {
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
