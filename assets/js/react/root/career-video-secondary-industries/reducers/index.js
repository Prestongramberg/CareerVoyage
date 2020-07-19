import { combineReducers } from "redux";
import subscriptions from "./subscriptions";
import uiState from "./uistate";

const rootReducer = combineReducers({
    subscriptions,
    uiState
});

export default rootReducer;
