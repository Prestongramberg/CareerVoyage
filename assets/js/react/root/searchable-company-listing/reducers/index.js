import { combineReducers } from "redux";
import companies from "./companies";
import industries from "./industries";
import search from "./search";
import user from "./user";

const rootReducer = combineReducers({
    companies,
    industries,
    search,
    user
});

export default rootReducer;
