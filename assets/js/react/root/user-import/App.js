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

    //const CalendarRef = useRef()

    useEffect(() => {
        // Some initialization logic here
        loadUsers();
    }, []);

 /*   useEffect(() => {

        UIkit.util.on('.uk-switcher', 'show', function (ev) {
            if ($(ev.target).hasClass('experience_schedule')) {
                loadSchedule(schedule);
            }
        });

    }, [])*/

   /* let today = new Date();
    let tomorrow = new Date()
    tomorrow.setDate(today.getDate() + 1);
*/
    debugger;

/*    const [schedule, setSchedule] = useState(props.schedule || {
        freq: 'WEEKLY',
        interval: 1,
        byDay: 'SU',
        byDayMultiple: [],
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

    const [events, setEvents] = useState([]);*/

   /* const onSubmit = (values) => {
        saveSchedule(values);
    };*/

    const [userItems, setUserItems] = useState([]);

    const loadUsers = () => {

        debugger;
        let url = window.Routing.generate("user_import_get_users", {});

        api.get(url)
            .then((response) => {
                if (response.statusCode < 300) {
                    debugger;
                    let items = response.responseBody.userItems;
                    setUserItems(items);
                }
            })
            .catch((e) => {
            })

    }

/*    const saveSchedule = (values) => {

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

    }*/


    return (
        <div className="uk-grid">
            {userItems.map((userItem) => {
                return (
                    <div className="uk-width-1-1">

                        <div className="uk-grid uk-margin-bottom">

                            <div className="uk-width-1-6">
                                <label>First Name<span className="uk-text-danger">*</span></label>
                                <input value={userItem.firstName} type="text" className="uk-input" />
                            </div>
                            <div className="uk-width-1-6">
                                <label>Last Name<span className="uk-text-danger">*</span></label>
                                <input value={userItem.lastName} type="text" className="uk-input" />
                            </div>
                            <div className="uk-width-1-6">
                                <label>Graduating Year<span className="uk-text-danger">*</span></label>
                                <input value={userItem.graduatingYear} type="text" className="uk-input" />
                            </div>
                            <div className="uk-width-1-6">
                                <label>Educator Email<span className="uk-text-danger">*</span></label>
                                <input value={userItem.educatorEmail} type="text" className="uk-input" />
                            </div>
                            <div className="uk-width-1-6">
                                <label>Username<span className="uk-text-danger">*</span></label>
                                <input value={userItem.username} type="text" className="uk-input" />
                            </div>
                            <div className="uk-width-1-6">
                                <label>Temp Password<span className="uk-text-danger">*</span></label>
                                <input value={userItem.tempPassword} type="text" className="uk-input" />
                            </div>
                        </div>
                    </div>
                );
            })}
        </div>
    );
}

App.propTypes = {
};

App.defaultProps = {
};
