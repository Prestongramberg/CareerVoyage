import * as actionTypes from "../actions/actionTypes";

export default (state = {}, action) => {

    switch (action.type) {
        case actionTypes.LOAD_INDUSTRIES_SUCCESS:
            return {
                ...state,
                data: action.response.data
            };
        case actionTypes.SUBSCRIBE:
            let industryAlreadyExists = state.subscribed.indexOf(action.industryId) > -1;
            return {
                ...state,
                subscribed: industryAlreadyExists ? state.subscribed : [
                    ...state.subscribed,
                    action.industryId
                ]
            };
        case actionTypes.UNSUBSCRIBE:
            return {
                ...state,
                subscribed: state.subscribed.filter(industryId => industryId !== action.industryId)
            };
        default:
            return state;
    }
};
