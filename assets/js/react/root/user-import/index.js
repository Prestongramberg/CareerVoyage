import React from "react";
import ReactDOM from "react-dom";
import App from "./App";


const userImport = document.getElementById("user-import");

if (userImport) {

    debugger;
/*    // todo need experience id right? Maybe not?
    const renderCalendar = !!eventSchedule.getAttribute("data-render-calendar");
    const experienceId = parseInt(eventSchedule.getAttribute("data-experience"));
    let schedule = eventSchedule.getAttribute("data-schedule") ? JSON.parse(eventSchedule.getAttribute("data-schedule")) : [];
    schedule = schedule == false ? null : schedule;

    if(schedule && schedule.startDate) {
        schedule.startDate = [new Date(schedule.startDate[0])];
    }

    if(schedule && schedule.until) {
        schedule.until = [new Date(schedule.until[0])];
    }*/

    const render = () => {
        ReactDOM.render(
            <App />,
            userImport
        );
    };
    render();
}
