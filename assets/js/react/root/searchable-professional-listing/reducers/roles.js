import * as actionTypes from "../actions/actionTypes";

export default (state = {}, action) => {
    switch (action.type) {
        case actionTypes.PROFESSIONALS_LOADING_SUCCESS:
            debugger;
            return get_roles_from_request( action.response.data );
        default:
            return state;
    }
};

function get_roles_from_request( professionals ) {
    const role_ids = [];
    const roles = [];
    
    professionals.forEach(professional => {

        if(professional.rolesWillingToFulfill) {
            professional.rolesWillingToFulfill.forEach(role => {
                if ( role_ids.indexOf( role.id ) === -1 ) {
                    role_ids.push(role.id);
                    roles.push(role);
                }
            });
        }
    });

    return roles.sort((a, b) => (a.name.toLowerCase() > b.name.toLowerCase()) ? 1 : -1);
}
