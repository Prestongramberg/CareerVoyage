import React from "react";
import {Field} from 'react-final-form';
import {OnChange} from 'react-final-form-listeners'
import "flatpickr/dist/themes/material_green.css";
import {getArrayOfTimes} from '../utilities/form-helper';
import Flatpickr from "react-flatpickr";
import Cleave from "cleave.js/react";
import Error from "./Error";

const EndFields = ({schedule, changeEndAction, changeCount, changeUntil}) => {

    const handleEndActionChange = (value, previous) => {
        const modifiedSchedule = {...schedule, endAction: value}
        changeEndAction(modifiedSchedule);
    };

    const handleCountChange = (value, previous) => {
        const modifiedSchedule = {...schedule, count: value}
        changeCount(modifiedSchedule);
    };

    const handleUntilChange = (value, previous) => {
        const modifiedSchedule = {...schedule, until: value}
        changeUntil(modifiedSchedule);
    };

    const dateRequired = (value) => (value.length ? undefined : "Required");
    const required = (value) => (value ? undefined : "Required");

    const renderUntilField = () => {
        return (
            <div>
                <Field className="uk-margin" name="until" validate={dateRequired}>
                    {props => (
                        <div>
                            <Flatpickr
                                name={props.input.name}
                                data-enable-time
                                value={props.input.value}
                                onChange={props.input.onChange}
                                className="uk-input uk-form-width-medium"
                                options={{
                                    dateFormat: "m/d/Y",
                                    minDate: "today",
                                    enableTime: false
                                }}
                            />
                            {props.meta.error && props.meta.touched && <div style={{color: "#f0506e", marginLeft: "150px"}}>{props.meta.error}</div>}
                        </div>
                    )}
                </Field>
                <OnChange name="until">
                    {handleUntilChange}
                </OnChange>
            </div>
        );
    }

    const renderEndActionField = () => {
        return (
            <div style={{marginRight: "20px", float: "left"}}>
                <Field className="uk-select uk-form-width-small" name="endAction" component="select">
                    <option value="Never">Never</option>
                    <option value="After">After</option>
                    <option value="On date">On date</option>
                </Field>
                <OnChange name="endAction">
                    {handleEndActionChange}
                </OnChange>
            </div>
        );
    };

    const renderCountField = () => {
        return (
            <div>
                <Field className="uk-margin" name="count" validate={required}>
                    {props => (
                        <div>
                            <Cleave
                                className="uk-input uk-form-width-small"
                                name={props.input.name}
                                value={props.input.value}
                                onChange={props.input.onChange}
                                maxLength="3"
                                options={{
                                    numeral: true,
                                    stripLeadingZeroes: true,
                                    numeralThousandsGroupStyle: 'none',
                                    numeralDecimalScale: 0
                                }}
                            />{" "}scheduled events
                            {props.meta.error && props.meta.touched && <div style={{color: "#f0506e", marginLeft: "150px"}}>{props.meta.error}</div>}
                        </div>
                    )}
                </Field>
                <OnChange name="count">
                    {handleCountChange}
                </OnChange>
            </div>
        );
    }

    return (
        <div className="uk-form-horizontal uk-clearfix">

            <div className="uk-margin">
                <label className="uk-form-label" htmlFor="form-horizontal-select">End</label>
                <div className="uk-form-controls">
                    {renderEndActionField()}
                    {schedule.endAction === 'After' && renderCountField()}
                    {schedule.endAction === 'On date' && renderUntilField()}
                </div>
            </div>

        </div>
    );
}

export default EndFields;
