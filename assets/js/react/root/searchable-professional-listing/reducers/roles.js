import * as actionTypes from "../actions/actionTypes";

export default (state = {}, action) => {
    switch (action.type) {
        case actionTypes.PROFESSIONALS_LOADING_SUCCESS:
            return get_roles_from_request( action.response.data );
        default:
            return state;
    }
};

function get_roles_from_request( professionals ) {
    return [];
}