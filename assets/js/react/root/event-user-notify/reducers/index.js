import { combineReducers } from "redux";
import companies from "./companies";
import industries from "./industries";
import users from "./users";
import search from "./search";
import ui from "./ui";
import form from "./form";
import experienceId from "./experienceId";

const rootReducer = combineReducers({
    companies,
    industries,
    users,
    search,
    ui,
    form,
    experienceId
});

export default rootReducer;
