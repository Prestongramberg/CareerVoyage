import { combineReducers } from "redux";
import lessons from "./lessons";
import industries from "./industries";
import search from "./search";

const rootReducer = combineReducers({
    lessons,
    industries,
    search
});

export default rootReducer;
