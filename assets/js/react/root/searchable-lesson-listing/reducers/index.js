import { combineReducers } from "redux";
import lessons from "./lessons";
import courses from "./courses";
import favorites from "./favorites";
import search from "./search";
import teachables from "./teachables";
import user from "./user";

const rootReducer = combineReducers({
    lessons,
    courses,
    favorites,
    search,
    teachables,
    user
});

export default rootReducer;
