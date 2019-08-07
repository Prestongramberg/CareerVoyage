import * as actionTypes from "../actions/actionTypes";

export default (state = {}, action) => {
    switch (action.type) {
        case actionTypes.COMPANIES_LOADING_SUCCESS:
            return get_industries_from_request( action.response.data );
        default:
            return state;
    }
};

function get_industries_from_request( companies ) {

    const industry_ids = [];
    const industries = [];

    companies.forEach(company => {
        if ( company.primaryIndustry && company.primaryIndustry.id && industry_ids.indexOf(company.primaryIndustry.id) === -1  ) {
            industry_ids.push(company.primaryIndustry.id);
            industries.push(company.primaryIndustry);
        }
        if(company.secondaryIndustries) {
            company.secondaryIndustries.forEach(secondary_industry => {
                if ( industry_ids.indexOf(secondary_industry.id) === -1  ) {
                    industry_ids.push(secondary_industry.id);
                    industries.push(secondary_industry);
                }
            })
        }
    });

    return industries;
}

