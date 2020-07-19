import { combineReducers } from "redux";
import companies from "./companies";
import companyVideos from "./companyVideos";
import careerVideos from "./careerVideos";
import companyVideoIndustries from "./companyVideoIndustries";
import careerVideoIndustries from "./careerVideoIndustries";
import companyVideoSearch from "./companyVideoSearch";
import careerVideoSearch from "./careerVideoSearch";
import search from "./search";
import user from "./user";

const rootReducer = combineReducers({
    companies,
    companyVideos,
    careerVideos,
    search,
    companyVideoIndustries,
    careerVideoIndustries,
    companyVideoSearch,
    careerVideoSearch,
    user
});

export default rootReducer;
