import { combineReducers } from "redux";
import schools from "./schools";
import search from "./search";
import user from "./user";

const rootReducer = combineReducers({
    schools,
    search,
    user
});

export default rootReducer;
