import { combineReducers } from "redux";
import companies from "./companies";
import companyVideos from "./companyVideos";
import careerVideos from "./careerVideos";
import professionalVideos from "./professionalVideos";
import companyVideoIndustries from "./companyVideoIndustries";
import careerVideoIndustries from "./careerVideoIndustries";
import professionalVideoIndustries from "./professionalVideoIndustries";
import companyVideoSearch from "./companyVideoSearch";
import careerVideoSearch from "./careerVideoSearch";
import professionalVideoSearch from "./professionalVideoSearch";
import search from "./search";
import user from "./user";

const rootReducer = combineReducers({
    companies,
    companyVideos,
    careerVideos,
    professionalVideos,
    search,
    companyVideoIndustries,
    careerVideoIndustries,
    professionalVideoIndustries,
    companyVideoSearch,
    careerVideoSearch,
    professionalVideoSearch,
    user
});

export default rootReducer;
