import { combineReducers } from "redux";
import companies from "./companies";
import industries from "./industries";
import search from "./search";

const rootReducer = combineReducers({
    companies,
    industries,
    search
});

export default rootReducer;
