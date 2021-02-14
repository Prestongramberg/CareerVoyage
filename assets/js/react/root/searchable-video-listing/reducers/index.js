import { combineReducers } from "redux";
import companies from "./companies";
import allVideos from "./allVideos";
import companyVideos from "./companyVideos";
import careerVideos from "./careerVideos";
import professionalVideos from "./professionalVideos";
import allVideoIndustries from "./allVideoIndustries";
import companyVideoIndustries from "./companyVideoIndustries";
import careerVideoIndustries from "./careerVideoIndustries";
import professionalVideoIndustries from "./professionalVideoIndustries";
import allVideoSearch from "./allVideoSearch";
import companyVideoSearch from "./companyVideoSearch";
import careerVideoSearch from "./careerVideoSearch";
import professionalVideoSearch from "./professionalVideoSearch";
import search from "./search";
import user from "./user";

const rootReducer = combineReducers({
    companies,
    allVideos,
    companyVideos,
    careerVideos,
    professionalVideos,
    search,
    allVideoIndustries,
    companyVideoIndustries,
    careerVideoIndustries,
    professionalVideoIndustries,
    allVideoSearch,
    companyVideoSearch,
    careerVideoSearch,
    professionalVideoSearch,
    user
});

export default rootReducer;
