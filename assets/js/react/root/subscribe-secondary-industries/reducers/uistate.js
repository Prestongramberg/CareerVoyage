import * as actionTypes from "../actions/actionTypes";

export default (state = {}, action) => {
    switch (action.type) {
        case actionTypes.LOAD_INDUSTRIES_FAILURE:
        case actionTypes.LOAD_SECONDARY_INDUSTRIES_FAILURE:
        case actionTypes.LOAD_INDUSTRIES_SUCCESS:
            return {
                ...state,
                loading: false
            };
        case actionTypes.LOAD_SECONDARY_INDUSTRIES_SUCCESS:
            return {
                ...state,
                loading: false
            };
        case actionTypes.PRIMARY_INDUSTRY_CHANGED:
            return {
                ...state,
                primaryIndustrySelected: parseInt( action.industryId )
            };
// TODO: This may need fixing
        case actionTypes.SECONDARY_INDUSTRY_SEARCHED:
            return {
                ...state,
                secondaryIndustrySearched: action.secondaryIndustrySearched
            }
        case actionTypes.SUBSCRIBE:
            return {
                ...state,
                secondaryIndustrySelected: ''
            };
        default:
            return state;
    }
};
