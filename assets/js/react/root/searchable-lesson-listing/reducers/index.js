import { combineReducers } from "redux";
import lessons from "./lessons";
import courses from "./courses";
import search from "./search";
import user from "./user";

const rootReducer = combineReducers({
    lessons,
    courses,
    search,
    user
});

export default rootReducer;
