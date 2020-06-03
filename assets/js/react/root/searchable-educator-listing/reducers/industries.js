import * as actionTypes from "../actions/actionTypes";

export default (state = {}, action) => {
    switch (action.type) {
        case actionTypes.EDUCATORS_LOADING_SUCCESS:
            return get_industries_from_request( action.response.data );
        default:
            return state;
    }
};

function get_industries_from_request( educators ) {
    const industry_ids = [];
    const secondary_industry_ids = [];
    const industries = [];

    educators.forEach(educator => {
        if ( educator.secondaryIndustries ) {

            // Add any non-existing secondary Industries
            educator.secondaryIndustries.forEach(secondary_industry => {
                if( industry_ids.indexOf(secondary_industry.primaryIndustry.id) === -1 ) {
                    industry_ids.push( secondary_industry.primaryIndustry.id );
                    industries.push( secondary_industry.primaryIndustry );
                    const primaryIndex = industries.length - 1;
                    industries[ primaryIndex ].secondaryIndustries = [];
                }
                if( secondary_industry_ids.indexOf(secondary_industry.id) === -1 ) {
                    const industryIndex = industries.findIndex( industry => industry.id === secondary_industry.primaryIndustry.id );
                    industries[ industryIndex ].secondaryIndustries.push( secondary_industry );
                    secondary_industry_ids.push(secondary_industry.id);
                }
            });

        }
    });

    return industries;
}
