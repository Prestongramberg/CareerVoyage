import { combineReducers } from "redux";
import calendar from "./calendar";
import events from "./events";
import search from "./search";
import filters from "./filters";

const rootReducer = combineReducers({
    calendar,
    events,
    search,
    filters
});

export default rootReducer;
