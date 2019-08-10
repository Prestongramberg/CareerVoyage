import * as actionTypes from "../actions/actionTypes";

export default (state = {}, action) => {
    switch (action.type) {
        case actionTypes.LOAD_INDUSTRIES_FAILURE:
        case actionTypes.LOAD_INDUSTRIES_SUCCESS:
            return {
                ...state,
                loading: false
            };
        case actionTypes.PRIMARY_INDUSTRY_CHANGED:
            return {
                ...state,
                primaryIndustrySelected: parseInt( action.industryId )
            };
        case actionTypes.SUBSCRIBE:
            return {
                ...state,
                secondaryIndustrySelected: ''
            };
        default:
            return state;
    }
};
