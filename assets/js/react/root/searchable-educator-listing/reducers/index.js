import { combineReducers } from "redux";
import educators from "./educators";
import industries from "./industries";
import schools from "./schools";
import search from "./search";

const rootReducer = combineReducers({
    educators,
    industries,
    schools,
    search
});

export default rootReducer;
