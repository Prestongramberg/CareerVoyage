import { combineReducers } from "redux";
import educators from "./educators";
import search from "./search";

const rootReducer = combineReducers({
    educators,
    search
});

export default rootReducer;
