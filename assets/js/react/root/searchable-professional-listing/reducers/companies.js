import * as actionTypes from "../actions/actionTypes";

export default (state = {}, action) => {
    switch (action.type) {
        case actionTypes.PROFESSIONALS_LOADING_SUCCESS:
            return get_companies_from_request( action.response.data );
        default:
            return state;
    }
};

function get_companies_from_request( professionals ) {
    const company_ids = [];
    const companies = [];

    professionals.forEach(professional => {
        if ( professional.company && professional.company.id && professional.company.newCompanyRequest.approved && company_ids.indexOf(professional.company.id) === -1 ) {
            company_ids.push(professional.company.id);
            companies.push(professional.company);
        }
    });

    return companies;
}