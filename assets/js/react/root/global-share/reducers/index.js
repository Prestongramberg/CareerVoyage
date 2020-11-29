import { combineReducers } from "redux";
import users from "./users";
import filters from "./filters";
import search from "./search";
import ui from "./ui";

const rootReducer = combineReducers({
    users,
    ui,
    filters,
    search
});

export default rootReducer;
