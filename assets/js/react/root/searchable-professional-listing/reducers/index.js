import { combineReducers } from "redux";
import professionals from "./professionals";
import search from "./search";

const rootReducer = combineReducers({
    professionals,
    search
});

export default rootReducer;
