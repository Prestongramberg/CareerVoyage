import React, {useState, useEffect, useRef, useCallback} from "react"
import PropTypes from "prop-types";
import ScheduleForm from "./components/ScheduleForm";
import FullCalendar from "@fullcalendar/react";
import dayGridPlugin from "@fullcalendar/daygrid";
import * as api from "../../utilities/api/api";
import * as actionTypes from "../global-share/actions/actionTypes";
import {useFormState} from 'react-final-form';
import experienceId from "../event-user-notify/reducers/experienceId";
import {deepObject} from "../../utilities/object-utils";

export default function App(props) {

    const CalendarRef = useRef()

    useEffect(() => {

        UIkit.util.on('.uk-switcher', 'show', function (ev) {
            if ($(ev.target).hasClass('experience_schedule')) {
                loadSchedule(schedule);
            }
        });

    }, [])

    let today = new Date();
    let tomorrow = new Date()
    tomorrow.setDate(today.getDate() + 1);

    debugger;

    const [schedule, setSchedule] = useState(props.schedule || {
        freq: 'WEEKLY',
        interval: 1,
        byDay: 'SU',
        byDayMultiple: ['SU'],
        byMonthDay: 1,
        monthlyType: 'onDay',
        yearlyType: 'on',
        bySetPos: 1,
        byMonth: 1,
        startDate: [today],
        startTime: '7:30 pm',
        endTime: '8:30 pm',
        endAction: 'Never',
        count: 1,
        until: [tomorrow]
    });

    const [events, setEvents] = useState([]);

    const onSubmit = (values) => {
        saveSchedule(values);
    };

    const loadSchedule = (values) => {

        let recurrenceRule = getRecurrenceRule(values);
        let startDate = values.startDate[0].toLocaleDateString("en-US");

        let url = window.Routing.generate("api_experience_get_schedule", {
            id: props.experienceId
        });

        let data = {
            recurrenceRule: recurrenceRule,
            startDate: startDate,
            schedule: values
        }

        api.post(url, data)
            .then((response) => {
                debugger;
                if (response.statusCode < 300 && response.responseBody.success === true) {
                    let newEvents = response.responseBody.dates;
                    setEvents(newEvents);
                }

            })
            .catch((e) => {
            })

    }

    const saveSchedule = (values) => {

        debugger;
        let recurrenceRule = getRecurrenceRule(values);
        let startDate = values.startDate[0].toLocaleDateString("en-US");

        let url = window.Routing.generate("api_experience_save_schedule", {
            id: props.experienceId
        });

        let data = {
            recurrenceRule: recurrenceRule,
            startDate: startDate,
            schedule: values
        }

        api.post(url, data)
            .then((response) => {
                debugger;
                if (response.statusCode < 300 && response.responseBody.success === true) {
                    UIkit.notification('Recurring event schedule saved. Preview calendar below.', {status: 'success', pos: 'top-center'})
                    let newEvents = response.responseBody.dates;
                    setEvents(newEvents);
                }

            })
            .catch((e) => {
            })

    }

    // todo 2 additional functions? State to string? Or string to state? Unless you want to just convert the state to a string and save it?
    //  this feels like the easier less scalable solution.

    const renderRecurrenceRuleHiddenInput = () => {

        return (
            <input type="hidden" name="recurrenceRule" value={getRecurrenceRule()}/>
        );
    };

    const getRecurrenceRule = (values) => {

        let currentSchedule = values || schedule;

        // todo NEED TO ADD A START DATE and a start time, count, and until for ending


        // todo add prop types and default props to child components????

        // todo process the state values here and create the rule
        let rrule = [];

        // todo need additional logic here if freq === weekly elseif freq === Monthly, etc

        rrule.push(`FREQ=${currentSchedule.freq}`);

        if (currentSchedule.endAction === 'After') {
            rrule.push(`COUNT=${currentSchedule.count}`);
        }

        if (currentSchedule.endAction === 'On date') {
            // todo need some type of date formatting here
            let isoDateString = currentSchedule.until[0].toISOString();
            isoDateString = isoDateString.replace(/:|-/g, '');
            isoDateString = isoDateString.split('.')[0] + 'Z';
            rrule.push(`UNTIL=${isoDateString}`);
        }

        if (currentSchedule.freq === 'YEARLY') {

            if (currentSchedule.yearlyType === 'on') {
                rrule.push(`BYMONTH=${currentSchedule.byMonth}`);
                rrule.push(`BYMONTHDAY=${currentSchedule.byMonthDay}`);
            }

            if (currentSchedule.yearlyType === 'onThe') {
                rrule.push(`BYDAY=${currentSchedule.byDay}`);
                rrule.push(`BYSETPOS=${currentSchedule.bySetPos}`);
                rrule.push(`BYMONTH=${currentSchedule.byMonth}`);
            }
        }

        if (currentSchedule.freq === 'MONTHLY') {

            if (currentSchedule.interval) {
                rrule.push(`INTERVAL=${currentSchedule.interval}`);
            }

            if (currentSchedule.monthlyType === 'onDay') {
                rrule.push(`BYMONTHDAY=${currentSchedule.byMonthDay}`);
            }

            if (currentSchedule.monthlyType === 'onThe') {
                rrule.push(`BYSETPOS=${currentSchedule.bySetPos}`);
                rrule.push(`BYDAY=${currentSchedule.byDay}`);
            }
        }

        if (currentSchedule.freq === 'WEEKLY') {

            if (currentSchedule.interval) {
                rrule.push(`INTERVAL=${currentSchedule.interval}`);
            }

            if (currentSchedule.byDay) {
                rrule.push(`BYDAY=${currentSchedule.byDayMultiple.join(',')}`);
            }
        }

        if (currentSchedule.freq === 'DAILY') {

            if (currentSchedule.interval) {
                rrule.push(`INTERVAL=${currentSchedule.interval}`);
            }

        }

        rrule = rrule.join(';');

        return rrule;
    }

    const renderCalendar = () => {

        return (
            <div>
                <FullCalendar
                    ref={CalendarRef}
                    height={500}
                    defaultView="dayGridMonth"
                    eventLimit={true}
                    editable={true}
                    selectable={true}
                    selectMirror={true}
                    dayMaxEvents={true}
                    events={events}
                    datesRender={(data) => {
                        debugger;
                    }}
                    eventClick={(event) => {
                        event.jsEvent.preventDefault(); // don't let the browser navigate
                        event = event.event._def;
                        if (event.url) {
                            window.open(event.url, "_blank");
                            return false;
                        }
                    }}
                    defaultDate={schedule.startDate[0] || new Date()}
                    header={{
                        left: 'prev,next',
                        center: 'title',
                        right: 'dayGridDay,dayGridWeek,dayGridMonth'
                    }}
                    plugins={[dayGridPlugin]}
                    timeZone={'America/Chicago'}
                />
            </div>
        );
    }

    return (
        <div>
            <ScheduleForm
                schedule={schedule}
                onSubmit={onSubmit}
                changeByDay={setSchedule}
                changeInterval={setSchedule}
                changeFreq={setSchedule}
                changeByMonthDay={setSchedule}
                changeMonthlyType={setSchedule}
                changeBySetPos={setSchedule}
                changeByMonth={setSchedule}
                changeYearlyType={setSchedule}
                changeByDayMultiple={setSchedule}
                changeStartDate={setSchedule}
                changeStartTime={setSchedule}
                changeEndTime={setSchedule}
                changeEndAction={setSchedule}
                changeCount={setSchedule}
                changeUntil={setSchedule}
            />
            {renderRecurrenceRuleHiddenInput()}
            <hr/>
            {props.renderCalendar && renderCalendar()}
        </div>
    );
}

App.propTypes = {
    experienceId: PropTypes.number.isRequired,
    schedule: PropTypes.object
};

App.defaultProps = {
    renderCalendar: false,
    schedule: null
};
