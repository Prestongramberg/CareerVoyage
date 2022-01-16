import React from "react";
import Cleave from 'cleave.js/react';
import {Field} from 'react-final-form';
import {OnChange} from 'react-final-form-listeners'

const DailyFields = ({schedule, changeInterval}) => {

    const handleIntervalChange = (value, previous) => {
        const modifiedSchedule = {...schedule, interval: value}
        changeInterval(modifiedSchedule);
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
                            />{" "}Day(s)
                        </div>
                    )}
                </Field>
                <OnChange name="interval">
                    {handleIntervalChange}
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

        </div>
    );
}

export default DailyFields;
