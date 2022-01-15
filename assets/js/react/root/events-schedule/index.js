import React from "react";
import ReactDOM from "react-dom";
import App from "./App";

/**
 * @see https://www.textmagic.com/free-tools/rrule-generator
 * @see https://github.com/simshaun/recurr
 */

const eventSchedule = document.getElementById("react-events-schedule");

if (eventSchedule) {

    debugger;
    // todo need experience id right? Maybe not?
    const renderCalendar = !!eventSchedule.getAttribute("data-render-calendar");
    const experienceId = parseInt(eventSchedule.getAttribute("data-experience"));
    let schedule = eventSchedule.getAttribute("data-schedule") ? JSON.parse(eventSchedule.getAttribute("data-schedule")) : [];
    schedule = schedule == false ? null : schedule;

    if(schedule && schedule.startDate) {
        schedule.startDate = [new Date(schedule.startDate[0])];
    }

    if(schedule && schedule.until) {
        schedule.until = [new Date(schedule.until[0])];
    }

    const render = () => {
        ReactDOM.render(
            <App
                renderCalendar={renderCalendar}
                experienceId={experienceId}
                schedule={schedule}
            />,
            eventSchedule
        );
    };
    render();
}
