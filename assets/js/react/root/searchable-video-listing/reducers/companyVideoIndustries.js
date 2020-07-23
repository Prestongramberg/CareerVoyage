import * as actionTypes from "../actions/actionTypes";

export default (state = {}, action) => {
    switch (action.type) {
        case actionTypes.VIDEOS_LOADING_SUCCESS:
            return get_industries_from_request( action.response.data );
        default:
            return state;
    }
};

function get_industries_from_request( videos ) {

    const industry_ids = [];
    const secondary_industry_ids = [];
    const industries = [];

    if(!videos.companyVideos) {
        return industries;
    }

    videos.companyVideos.forEach(video => {

        if(video.company) {

            let company = video.company;

            if ( company.primaryIndustry && company.primaryIndustry.id) {

                let primaryIndex = industry_ids.indexOf(company.primaryIndustry.id);

                // Check for the first instance of the primary Industry
                if( primaryIndex === -1 ) {
                    industry_ids.push(company.primaryIndustry.id);
                    industries.push(company.primaryIndustry);
                    primaryIndex = industries.length - 1;
                    industries[ primaryIndex ].secondaryIndustries = [];

                }

                // Add any non-existing secondary Industries
                company.secondaryIndustries.forEach(secondary_industry => {
                    if( secondary_industry_ids.indexOf(secondary_industry.id) === -1 ) {
                        secondary_industry_ids.push(secondary_industry.id);
                        industries[ primaryIndex ].secondaryIndustries.push(secondary_industry);
                    }
                });
            }
        }
    });

    return industries.sort((a, b) => (a.name > b.name) ? 1 : -1);
}

