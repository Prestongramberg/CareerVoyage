import { combineReducers } from "redux";
import lessons from "./lessons";
import courses from "./courses";
import search from "./search";

const rootReducer = combineReducers({
    lessons,
    courses,
    search
});

export default rootReducer;
