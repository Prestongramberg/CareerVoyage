import React from "react";
import ReactDOM from "react-dom";
import App from "./App";

/**
 * @see https://www.textmagic.com/free-tools/rrule-generator
 * @see https://github.com/simshaun/recurr
 */

const eventSchedule = document.getElementById("react-events-schedule");

if (eventSchedule) {

    // todo need experience id right? Maybe not?
    const renderCalendar = !!eventSchedule.getAttribute("data-render-calendar");

    const render = () => {
        ReactDOM.render(
            <App
                renderCalendar={renderCalendar}
            />,
            eventSchedule
        );
    };
    render();
}
