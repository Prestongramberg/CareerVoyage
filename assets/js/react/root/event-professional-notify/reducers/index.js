import { combineReducers } from "redux";
import companies from "./companies";
import industries from "./industries";
import professionals from "./professionals";
import search from "./search";
import ui from "./ui";
import form from "./form";
import experienceId from "./experienceId";

const rootReducer = combineReducers({
    companies,
    industries,
    professionals,
    search,
    ui,
    form,
    experienceId
});

export default rootReducer;
