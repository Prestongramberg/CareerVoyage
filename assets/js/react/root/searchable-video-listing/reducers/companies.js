import * as actionTypes from "../actions/actionTypes";
import {shuffle} from "../../../utilities/array-utils";

export default (state = {}, action) => {
    switch (action.type) {
        case actionTypes.VIDEOS_LOADING_SUCCESS:
            return get_companies_from_request( action.response.data );
        default:
            return state;
    }
};


function get_companies_from_request( videos ) {

    const company_ids = [];
    const companies = [];

    if(!videos.companyVideos) {
        return companies;
    }

    videos.companyVideos.forEach(companyVideo => {
        if ( companyVideo.company && companyVideo.company.id && company_ids.indexOf(companyVideo.company.id) === -1 ) {
            company_ids.push(companyVideo.company.id);
            companies.push(companyVideo.company);
        }
    });

    return companies.sort((a, b) => (a.name > b.name) ? 1 : -1);
}

