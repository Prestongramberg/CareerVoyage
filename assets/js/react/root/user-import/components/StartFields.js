import React from "react";
import {Field} from 'react-final-form';
import {OnChange} from 'react-final-form-listeners'
import "flatpickr/dist/themes/material_green.css";
import {getArrayOfTimes} from '../utilities/form-helper';
import Flatpickr from "react-flatpickr";
import Error from "./Error";

const StartFields = ({schedule, changeStartDate, changeStartTime, changeEndTime}) => {

    const handleStartDateChange = (value, previous) => {
        const modifiedSchedule = {...schedule, startDate: value}
        changeStartDate(modifiedSchedule);
    };

    const handleStartTimeChange = (value, previous) => {
        const modifiedSchedule = {...schedule, startTime: value}
        changeStartTime(modifiedSchedule);
    };

    const handleEndTimeChange = (value, previous) => {
        const modifiedSchedule = {...schedule, endTime: value}
        changeEndTime(modifiedSchedule);
    };

    const dateRequired = (value) => (value.length ? undefined : "Required");

    const renderStartDateField = () => {
        return (
            <div>
                <Field className="uk-margin" name="startDate" validate={dateRequired}>
                    {props => (
                        <div>
                            <Flatpickr
                                name={props.input.name}
                                data-enable-time
                                value={props.input.value}
                                onChange={props.input.onChange}
                                className="uk-input uk-form-width-small"
                                options={{
                                    dateFormat: "m/d/Y",
                                    enableTime: false
                                    /*minDate: "today"*/
                                }}
                            />
                            <Error name="startDate"/>
                        </div>
                    )}
                </Field>
                <OnChange name="startDate">
                    {handleStartDateChange}
                </OnChange>
            </div>
        );
    }

    const renderStartTimeField = () => {

        return (
            <div>
                <Field className="uk-select uk-form-width-small" name="startTime" component="select">
                    {getArrayOfTimes().map((time, i) => {
                        return (
                            <option key={i} value={time}>{time}</option>
                        )
                    })}
                </Field>
                <OnChange name="startTime">
                    {handleStartTimeChange}
                </OnChange>
            </div>
        );
    }

    const renderEndTimeField = () => {

        return (
            <div>
                <Field className="uk-select uk-form-width-small" name="endTime" component="select">
                    {getArrayOfTimes().map((time, i) => {
                        return (
                            <option key={i} value={time}>{time}</option>
                        )
                    })}
                </Field>
                <OnChange name="endTime">
                    {handleEndTimeChange}
                </OnChange>
            </div>
        );
    }

    return (
        <div className="uk-form-horizontal uk-clearfix">

            <div className="uk-margin">
                <label className="uk-form-label" htmlFor="form-horizontal-select">Start Date</label>
                <div className="uk-form-controls">
                    {renderStartDateField()}
                </div>
            </div>

            <div className="uk-margin">
                <label className="uk-form-label" htmlFor="form-horizontal-select">Start Time</label>
                <div className="uk-form-controls">
                    {renderStartTimeField()}
                </div>
            </div>

            <div className="uk-margin">
                <label className="uk-form-label" htmlFor="form-horizontal-select">End Time</label>
                <div className="uk-form-controls">
                    {renderEndTimeField()}
                </div>
            </div>

        </div>
    );
}

export default StartFields;
