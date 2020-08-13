import { combineReducers } from "redux";
import educators from "./educators";
import industries from "./industries";
import schools from "./schools";
import search from "./search";
import courses from "./courses";

const rootReducer = combineReducers({
    educators,
    industries,
    schools,
    courses,
    search
});

export default rootReducer;
