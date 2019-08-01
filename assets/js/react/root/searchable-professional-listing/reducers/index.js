import { combineReducers } from "redux";
import companies from "./companies";
import industries from "./industries";
import professionals from "./professionals";
import roles from "./roles";
import search from "./search";

const rootReducer = combineReducers({
    companies,
    industries,
    professionals,
    roles,
    search
});

export default rootReducer;
