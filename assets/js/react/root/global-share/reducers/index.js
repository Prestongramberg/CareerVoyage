import { combineReducers } from "redux";
import filters from "./filters";
import search from "./search";
import form from "./form";

const rootReducer = combineReducers({
    filters,
    search,
    form
});

export default rootReducer;
