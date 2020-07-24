import { combineReducers } from "redux";
import calendar from "./calendar";
import events from "./events";
import industries from "./industries";
import search from "./search";

const rootReducer = combineReducers({
    calendar,
    events,
    industries,
    search
});

export default rootReducer;
