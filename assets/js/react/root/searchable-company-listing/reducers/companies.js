import * as actionTypes from "../actions/actionTypes";

export default (state = {}, action) => {
    switch (action.type) {
        case actionTypes.COMPANIES_LOADING_SUCCESS:
            return action.response.data;
        case actionTypes.COMPANY_FAVORITE_SUCCESS:
        case actionTypes.COMPANY_UNFAVORITE_SUCCESS:
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
