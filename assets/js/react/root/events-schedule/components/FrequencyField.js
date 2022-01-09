import React from "react";
import {Field} from 'react-final-form';
import {OnChange} from 'react-final-form-listeners'

const FrequencyField = ({schedule, changeFreq}) => {

    const handleFreqChange = (value, previous) => {
        const modifiedSchedule = {...schedule, freq: value}
        changeFreq(modifiedSchedule);
    };

    return (
        <div className="uk-form-horizontal uk-margin uk-clearfix">
            <label className="uk-form-label" htmlFor="form-horizontal-select">Repeat</label>
            <div className="uk-form-controls">
                <Field className="uk-select uk-form-width-small" name="freq" component="select">
                    <option value="YEARLY">Yearly</option>
                    <option value="MONTHLY">Monthly</option>
                    <option value="WEEKLY">Weekly</option>
                    <option value="DAILY">Daily</option>
                </Field>
                <OnChange name="freq">
                    {handleFreqChange}
                </OnChange>
            </div>
        </div>
    );
}

export default FrequencyField;
