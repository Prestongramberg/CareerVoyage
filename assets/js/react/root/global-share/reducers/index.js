import { combineReducers } from "redux";
import users from "./users";
import ui from "./ui";

const rootReducer = combineReducers({
    users,
    ui
});

export default rootReducer;
