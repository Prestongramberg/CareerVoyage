import * as actionTypes from "../actions/actionTypes";

export default (state = {}, action) => {
    switch (action.type) {
        case actionTypes.VIDEOS_LOADING_SUCCESS:
            return get_industries_from_request( action.response.data );
        default:
            return state;
    }
};

function get_industries_from_request( professionals ) {

    const industry_ids = [];
    const secondary_industry_ids = [];
    const industries = [];

    professionals.forEach(professional => {
        if ( professional.primaryIndustry && professional.primaryIndustry.id) {

            let primaryIndex = industry_ids.indexOf(professional.primaryIndustry.id);

            // Check for the first instance of the primary Industry
            if( primaryIndex === -1 ) {
                industry_ids.push(professional.primaryIndustry.id);
                industries.push(professional.primaryIndustry);
                primaryIndex = industries.length - 1;
                industries[ primaryIndex ].secondaryIndustries = [];

            }

            // Add any non-existing secondary Industries
            professional.secondaryIndustries.forEach(secondary_industry => {
                if( secondary_industry_ids.indexOf(secondary_industry.id) === -1 ) {
                    secondary_industry_ids.push(secondary_industry.id);
                    industries[ primaryIndex ].secondaryIndustries.push(secondary_industry);
                }
            });

        }
    });

    return industries.sort((a, b) => (a.name > b.name) ? 1 : -1);
}

