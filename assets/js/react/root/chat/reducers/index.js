import { combineReducers } from "redux";
import chat from "./chat";
import ui from "./ui";

const rootReducer = combineReducers({
    chat,
    ui
});

export default rootReducer;
