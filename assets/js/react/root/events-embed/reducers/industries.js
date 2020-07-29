import * as actionTypes from "../actions/actionTypes";

export default (state = {}, action) => {
    switch (action.type) {
        case actionTypes.EVENTS_LOADING_SUCCESS:
            return get_industries_from_request( action.response.data );
        default:
            return state;
    }
};

function get_industries_from_request( events ) {
    const industry_ids = [];
    const secondary_industry_ids = [];
    const industries = [];

    events.forEach(event => {
        if ( event.secondaryIndustries && event.secondaryIndustries.length > 0) {
            event.secondaryIndustries.forEach(secondary_industry => {

                let primaryIndex = industry_ids.indexOf(secondary_industry.primaryIndustry.id);

                // Check for the first instance of the primary Industry
                if( primaryIndex === -1 ) {
                    industry_ids.push(secondary_industry.primaryIndustry.id);
                    industries.push(secondary_industry.primaryIndustry);
                    primaryIndex = industries.length - 1;
                    industries[ primaryIndex ].secondaryIndustries = [];
                }

                // Check for the first instance of the secondary Industry
                if( secondary_industry_ids.indexOf(secondary_industry.id) === -1 ) {
                    secondary_industry_ids.push(secondary_industry.id);
                    industries[ primaryIndex ].secondaryIndustries.push(secondary_industry);
                }

            })
        }
    });

    return industries;
}
