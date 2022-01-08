import React, {Component} from "react";
import PropTypes, {number} from "prop-types";
import Cleave from 'cleave.js/react';
import { reduxForm, Field } from 'redux-form';

const WeeklyForm = (props) => {

    const { handleSubmit } = props;

    return (
        <div>
            <form onSubmit={handleSubmit}>
                <fieldset className="uk-fieldset">
                    Every <Cleave
                    className="uk-input uk-form-width-small"
                    maxLength="3"
                    options={{numeral: true, stripLeadingZeroes: true, numeralThousandsGroupStyle: 'none', numeralDecimalScale: 0}}
                /> Week(s)

                    <div className="uk-margin uk-grid-small uk-child-width-auto uk-grid">
                        <label><Field className="uk-checkbox" id="SU" value="SU" name="byDay" component="input" type="checkbox"/> Sun</label>
                        <label><Field className="uk-checkbox" id="MO" value="MO" name="byDay" component="input" type="checkbox"/> Mon</label>
                        <label><Field className="uk-checkbox" id="TU" value="TU" name="byDay" component="input" type="checkbox"/> Tue</label>
                        <label><Field className="uk-checkbox" id="WE" value="WE" name="byDay" component="input" type="checkbox"/> Wed</label>
                        <label><Field className="uk-checkbox" id="TH" value="TH" name="byDay" component="input" type="checkbox"/> Thu</label>
                        <label><Field className="uk-checkbox" id="FR" value="FR" name="byDay" component="input" type="checkbox"/> Fri</label>
                        <label><Field className="uk-checkbox" id="SA" value="SA" name="byDay" component="input" type="checkbox"/> Sat</label>
                    </div>
                </fieldset>

                <button className="button is-link">Submit</button>
            </form>
        </div>
    );
}

export default reduxForm({
    form: 'signIn',
})(WeeklyForm);
