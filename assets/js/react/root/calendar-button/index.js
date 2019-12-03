import React from "react";
import ReactDOM from "react-dom";
import CalendarButton from "../../components/CalendarButton/CalendarButton";
import moment from "../../../vendor/moment"

const calendar_buttons = document.getElementsByClassName("react-calendar-button");
for( let i = 0; i < calendar_buttons.length; i++) {

    const eventStartTime = parseInt(calendar_buttons[i].getAttribute("data-event-start-time"));
    const eventEndTime = parseInt(calendar_buttons[i].getAttribute("data-event-end-time"));
    const eventTitle = calendar_buttons[i].getAttribute("data-title");
    const eventLocation = calendar_buttons[i].getAttribute("data-location");
    const eventDescription = calendar_buttons[i].getAttribute("data-description");

    ReactDOM.render(
        <CalendarButton
            description={eventDescription}
            endTime={eventEndTime}
            location={eventLocation}
            startTime={eventStartTime}
            title={eventTitle}
        />,
        calendar_buttons[i]
    );
}