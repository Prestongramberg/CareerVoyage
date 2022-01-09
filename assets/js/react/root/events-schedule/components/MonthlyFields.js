import React from "react";
import Cleave from 'cleave.js/react';
import {Field} from 'react-final-form';
import {OnChange} from 'react-final-form-listeners'

const MonthlyFields = ({
                           schedule,
                           changeByDay,
                           changeInterval,
                           changeByMonthDay,
                           changeBySetPos,
                           changeMonthlyType
                       }) => {

    const handleIntervalChange = (value, previous) => {
        const modifiedSchedule = {...schedule, interval: value}
        changeInterval(modifiedSchedule);
    };

    const handleMonthlyTypeChange = (value, previous) => {
        const modifiedSchedule = {...schedule, monthlyType: value}
        changeMonthlyType(modifiedSchedule);
    }

    const handleByMonthDayChange = (value, previous) => {
        const modifiedSchedule = {...schedule, byMonthDay: value}
        changeByMonthDay(modifiedSchedule);
    };

    const handleBySetPosChange = (value, previous) => {
        const modifiedSchedule = {...schedule, bySetPos: value}
        changeBySetPos(modifiedSchedule);
    };

    const handleByDayChange = (value, previous) => {
        const modifiedSchedule = {...schedule, byDay: value}
        changeByDay(modifiedSchedule);
    };

    const renderIntervalField = () => {
        return (
            <div>
                <Field className="uk-margin" name="interval">
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
                            />{" "}Months(s)
                        </div>
                    )}
                </Field>
                <OnChange name="interval">
                    {handleIntervalChange}
                </OnChange>
            </div>
        );
    }

    const renderOnDayField = () => {
        return (
            <label className="uk-form-label" htmlFor="form-horizontal-text">
                <Field
                    className="uk-radio"
                    name="monthlyType"
                    component="input"
                    type="radio"
                    value="onDay"
                />{" "}
                on day
                <OnChange name="monthlyType">
                    {handleMonthlyTypeChange}
                </OnChange>
            </label>
        );
    }

    const renderOnTheField = () => {
        return (
            <label className="uk-form-label" htmlFor="form-horizontal-text">
                <Field
                    className="uk-radio"
                    name="monthlyType"
                    component="input"
                    type="radio"
                    value="onThe"
                />{" "}
                on the
                <OnChange name="monthlyType">
                    {handleMonthlyTypeChange}
                </OnChange>
            </label>
        );
    }

    const renderByMonthDayField = () => {
        return (
            <div>
                <Field className="uk-select uk-form-width-small" name="byMonthDay" component="select">
                    {Array.from({length: 31}, (_, index) => index + 1).map((x, i) =>
                        <option key={i} value={x}>{x}</option>
                    )}
                </Field>
                <OnChange name="byMonthDay">
                    {handleByMonthDayChange}
                </OnChange>
            </div>
        )
    }

    const renderBySetPosField = () => {
        return (
            <div style={{marginRight: "20px", float: "left"}}>
                <Field className="uk-select uk-form-width-small" name="bySetPos" component="select">
                    <option value="1">First</option>
                    <option value="2">Second</option>
                    <option value="3">Third</option>
                    <option value="4">Fourth</option>
                    <option value="-1">Last</option>
                </Field>
                <OnChange name="bySetPos">
                    {handleBySetPosChange}
                </OnChange>
            </div>
        );
    }

    const renderByDayField = () => {
        return (
            <div>
                <Field className="uk-select uk-form-width-small" name="byDay" component="select">
                    <option value="SU">Sunday</option>
                    <option value="MO">Monday</option>
                    <option value="TU">Tuesday</option>
                    <option value="WE">Wednesday</option>
                    <option value="TH">Thursday</option>
                    <option value="FR">Friday</option>
                    <option value="SA">Saturday</option>
                    <option value="SU,MO,TU,WE,TH,FR,SA">Day</option>
                    <option value="MO,TU,WE,TH,FR">Weekday</option>
                    <option value="SU,SA">Weekend day</option>
                </Field>
                <OnChange name="byDay">
                    {handleByDayChange}
                </OnChange>
            </div>
        );
    }

    return (
        <div className="uk-form-horizontal uk-clearfix">

            <div className="uk-margin">
                <label className="uk-form-label" htmlFor="form-horizontal-select">Every</label>
                <div className="uk-form-controls">
                    {renderIntervalField()}
                </div>
            </div>

            <div className="uk-margin">
                {renderOnDayField()}
                <div className="uk-form-controls">
                    {renderByMonthDayField()}
                </div>
            </div>

            <div className="uk-margin">
                {renderOnTheField()}
                <div className="uk-form-controls">
                    {renderBySetPosField()}
                    {renderByDayField()}
                </div>
            </div>

        </div>
    );

}

export default MonthlyFields;
