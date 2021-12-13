import * as actionTypes from "../actions/actionTypes";

export default (state = {}, action) => {
    switch (action.type) {
        case actionTypes.EVENTS_LOADING_SUCCESS:
            return get_industries_from_request(action.response.data);
        default:
            return state;
    }
};

function get_industries_from_request(events) {
    debugger;
    const industry_ids = [];
    const secondary_industry_ids = [];
    const industries = [];

    events.forEach(event => {
        if (event.secondaryIndustries && event.secondaryIndustries.length > 0) {
            event.secondaryIndustries.forEach(secondary_industry => {

                let primaryIndex = industry_ids.indexOf(secondary_industry.primaryIndustry.id);

                // Check for the first instance of the primary Industry
                if (primaryIndex === -1) {
                    industry_ids.push(secondary_industry.primaryIndustry.id);
                    industries.push(secondary_industry.primaryIndustry);
                    primaryIndex = industries.length - 1;
                    industries[primaryIndex].secondaryIndustries = [];
                }

                // Check for the first instance of the secondary Industry
                if (secondary_industry_ids.indexOf(secondary_industry.id) === -1) {
                    secondary_industry_ids.push(secondary_industry.id);
                    industries[primaryIndex].secondaryIndustries.push(secondary_industry);
                }

            })
        }

        if (event.tags && event.tags.length > 0) {
            event.tags.forEach(tag => {

                let primaryIndex = null;
                let primaryIndustry = null;
                let primaryIndustryId = null;

                if (tag.primaryIndustry) {
                    primaryIndustry = tag.primaryIndustry;
                    primaryIndustryId = tag.primaryIndustry.id;
                    primaryIndex = industry_ids.indexOf(primaryIndustryId);
                } else if (tag.secondaryIndustry) {
                    primaryIndustry = tag.secondaryIndustry.primaryIndustry;
                    primaryIndustryId = tag.secondaryIndustry.primaryIndustry.id;
                    primaryIndex = industry_ids.indexOf(primaryIndustryId);
                }


                // Check for the first instance of the primary Industry
                if (primaryIndex === -1) {
                    industry_ids.push(primaryIndustryId);
                    industries.push(primaryIndustry);
                    primaryIndex = industries.length - 1;
                    industries[primaryIndex].secondaryIndustries = [];
                }

                if(tag.secondaryIndustry) {
                    // Check for the first instance of the secondary Industry
                    if (secondary_industry_ids.indexOf(tag.secondaryIndustry.id) === -1) {
                        secondary_industry_ids.push(tag.secondaryIndustry.id);
                        industries[primaryIndex].secondaryIndustries.push(tag.secondaryIndustry);
                    }
                }

            })
        }
    });

    return industries;
}
