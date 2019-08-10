import * as actionTypes from "../actions/actionTypes";

export default (state = {}, action) => {

    switch (action.type) {
        case actionTypes.LOAD_INDUSTRIES_SUCCESS:
            return {
                ...state,
                data: action.response.data
            };
        case actionTypes.SUBSCRIBE_SUCCESS:
            let industryAlreadyExists = state.subscribed.indexOf(action.industryId) > -1;
            return {
                ...state,
                subscribed: industryAlreadyExists ? state.subscribed : [
                    ...state.subscribed,
                    action.industryId
                ]
            };
        default:
            return state;
    }
};
