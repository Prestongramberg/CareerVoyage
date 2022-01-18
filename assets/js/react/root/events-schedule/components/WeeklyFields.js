import React from "react";
import Cleave from 'cleave.js/react';
import {Field} from 'react-final-form';
import {OnChange} from 'react-final-form-listeners'
import Error from './Error';

const WeeklyFields = ({schedule, changeByDayMultiple, changeInterval}) => {

    const handleIntervalChange = (value, previous) => {
        const modifiedSchedule = {...schedule, interval: value}
        changeInterval(modifiedSchedule);
    };

    const handleByDayMultipleChange = (value, previous) => {
        const modifiedSchedule = {...schedule, byDayMultiple: value}
        changeByDayMultiple(modifiedSchedule);
    }

    const required = (value) => (value ? undefined : "Required");

    const renderIntervalField = () => {
        return (
            <div>
                <Field className="uk-margin" name="interval" validate={required}>
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
                            />{" "}Weeks(s)
                            <Error name="interval"/>
                        </div>
                    )}
                </Field>
                <OnChange name="interval">
                    {handleIntervalChange}
                </OnChange>
            </div>
        );
    }

    const renderByDayMultipleField = () => {
        return (
            <div>
                <div className="uk-margin uk-grid-small uk-child-width-auto uk-grid">
                    <label htmlFor="SU">
                        <Field
                            className="uk-checkbox"
                            name="byDayMultiple"
                            component="input"
                            type="checkbox"
                            value="SU"
                            id="SU"
                        /> Sun
                    </label>
                    <label htmlFor="MO">
                        <Field
                            className="uk-checkbox"
                            name="byDayMultiple"
                            component="input"
                            type="checkbox"
                            value="MO"
                            id="MO"
                        /> Mon
                    </label>
                    <label htmlFor="TU">
                        <Field
                            className="uk-checkbox"
                            name="byDayMultiple"
                            component="input"
                            type="checkbox"
                            value="TU"
                            id="TU"
                        /> Tue
                    </label>
                    <label htmlFor="WE">
                        <Field
                            className="uk-checkbox"
                            name="byDayMultiple"
                            component="input"
                            type="checkbox"
                            value="WE"
                            id="WE"
                        /> Wed
                    </label>
                    <label htmlFor="TH">
                        <Field
                            className="uk-checkbox"
                            name="byDayMultiple"
                            component="input"
                            type="checkbox"
                            value="TH"
                            id="TH"
                        /> Thu
                    </label>
                    <label htmlFor="FR">
                        <Field
                            className="uk-checkbox"
                            name="byDayMultiple"
                            component="input"
                            type="checkbox"
                            value="FR"
                            id="FR"
                        /> Fri
                    </label>
                    <label htmlFor="SA">
                        <Field
                            className="uk-checkbox"
                            name="byDayMultiple"
                            component="input"
                            type="checkbox"
                            value="SA"
                            id="SA"
                        /> Sat
                    </label>
                    <OnChange name="byDayMultiple">
                        {handleByDayMultipleChange}
                    </OnChange>
                </div>
                <Error name="byDayMultiple"/>
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
                <label className="uk-form-label" htmlFor="form-horizontal-select">On day(s)</label>
                <div className="uk-form-controls">
                    {renderByDayMultipleField()}
                </div>
            </div>

        </div>
    );
}

export default WeeklyFields;
