import React, {useState, useEffect, useRef} from "react"
import PropTypes from "prop-types";
import ScheduleForm from "./components/ScheduleForm";
import FullCalendar from "@fullcalendar/react";
import dayGridPlugin from "@fullcalendar/daygrid";
import * as api from "../../utilities/api/api";
import * as actionTypes from "../global-share/actions/actionTypes";

export default function App(props) {

    const CalendarRef = useRef()

    useEffect(() => {
    }, [])

    let today = new Date();
    let tomorrow = new Date()
    tomorrow.setDate(today.getDate() + 1);

    const [schedule, setSchedule] = useState({
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
        startTime: '7:30PM',
        endTime: '8:30PM',
        endAction: 'Never',
        count: 1,
        until: [tomorrow]
    });

    const [events, setEvents] = useState([]);

    const onSubmit = (values) => {

        debugger;
        let recurrenceRule = getRecurrenceRule();
        let startDate = schedule.startDate[0].toLocaleDateString("en-US");

        let url = window.Routing.generate("get_dates_for_recurrence_rule");

        let data = {
            recurrenceRule: recurrenceRule,
            startDate: startDate
        }

        return api.post(url, data)
            .then((response) => {
                debugger;
                if (response.statusCode < 300 && response.responseBody.success === true) {
                    let newEvents = response.responseBody.dates;
                    setEvents(newEvents);
                }

            })
            .catch((e) => {

                debugger;

            })
    };


    // todo 2 additional functions? State to string? Or string to state? Unless you want to just convert the state to a string and save it?
    //  this feels like the easier less scalable solution.

    const renderRecurrenceRuleHiddenInput = () => {

        return (
            <input type="hidden" name="recurrenceRule" value={getRecurrenceRule()}/>
        );
    };

    const getRecurrenceRule = () => {

        // todo NEED TO ADD A START DATE and a start time, count, and until for ending


        // todo add prop types and default props to child components????

        // todo process the state values here and create the rule
        let rrule = [];

        // todo need additional logic here if freq === weekly elseif freq === Monthly, etc

        rrule.push(`FREQ=${schedule.freq}`);

        if (schedule.endAction === 'After') {
            rrule.push(`COUNT=${schedule.count}`);
        }

        if (schedule.endAction === 'On date') {
            // todo need some type of date formatting here
            let isoDateString = schedule.until[0].toISOString();
            isoDateString = isoDateString.replace(/:|-/g, '');
            isoDateString = isoDateString.split('.')[0] + 'Z';
            rrule.push(`UNTIL=${isoDateString}`);
        }

        if (schedule.freq === 'YEARLY') {

            if (schedule.yearlyType === 'on') {
                rrule.push(`BYMONTH=${schedule.byMonth}`);
                rrule.push(`BYMONTHDAY=${schedule.byMonthDay}`);
            }

            if (schedule.yearlyType === 'onThe') {
                rrule.push(`BYDAY=${schedule.byDay}`);
                rrule.push(`SETPOS=${schedule.bySetPos}`);
                rrule.push(`BYMONTH=${schedule.byMonth}`);
            }
        }

        if (schedule.freq === 'MONTHLY') {

            if (schedule.interval) {
                rrule.push(`INTERVAL=${schedule.interval}`);
            }

            if (schedule.monthlyType === 'onDay') {
                rrule.push(`BYMONTHDAY=${schedule.byMonthDay}`);
            }

            if (schedule.monthlyType === 'onThe') {
                rrule.push(`SETPOS=${schedule.bySetPos}`);
                rrule.push(`BYDAY=${schedule.byDay}`);
            }
        }

        if (schedule.freq === 'WEEKLY') {

            if (schedule.interval) {
                rrule.push(`INTERVAL=${schedule.interval}`);
            }

            if (schedule.byDay) {
                rrule.push(`BYDAY=${schedule.byDayMultiple.join(',')}`);
            }
        }

        if (schedule.freq === 'DAILY') {

            if (schedule.interval) {
                rrule.push(`INTERVAL=${schedule.interval}`);
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
                    events={events}
                    datesRender={(data) => {
                        debugger;
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

/*App.propTypes = {
    rrule: PropTypes.object,
    userId: PropTypes.number
};*/

App.defaultProps = {
    renderCalendar: false
};
