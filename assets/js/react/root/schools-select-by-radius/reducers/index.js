import { combineReducers } from "redux";
import search from "./search";
import schools from "./schools";

const rootReducer = combineReducers({
    search,
    schools
});

export default rootReducer;
