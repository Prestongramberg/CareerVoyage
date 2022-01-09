import React from "react";
import {Field} from 'react-final-form';
import {OnChange} from 'react-final-form-listeners'

const YearlyFields = ({
                          schedule,
                          changeByDay,
                          changeByMonth,
                          changeByMonthDay,
                          changeBySetPos,
                          changeYearlyType
                      }) => {


    const handleYearlyTypeChange = (value, previous) => {
        const modifiedSchedule = {...schedule, yearlyType: value}
        changeYearlyType(modifiedSchedule);
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

    const handleByMonthChange = (value, previous) => {
        const modifiedSchedule = {...schedule, byMonth: value}
        changeByMonth(modifiedSchedule);
    };

    const renderOnField = () => {
        return (
            <label className="uk-form-label" htmlFor="form-horizontal-text">
                <Field
                    className="uk-radio"
                    name="yearlyType"
                    component="input"
                    type="radio"
                    value="on"
                />{" "}
                on
                <OnChange name="yearlyType">
                    {handleYearlyTypeChange}
                </OnChange>
            </label>
        );
    }

    const renderOnTheField = () => {
        return (
            <label className="uk-form-label" htmlFor="form-horizontal-text">
                <Field
                    className="uk-radio"
                    name="yearlyType"
                    component="input"
                    type="radio"
                    value="onThe"
                />{" "}
                on the
                <OnChange name="yearlyType">
                    {handleYearlyTypeChange}
                </OnChange>
            </label>
        );
    }

    const renderByMonthField = () => {
        return (
            <div style={{float: "left", marginRight: "20px"}}>
                <Field className="uk-select uk-form-width-small" name="byMonth" component="select">
                    <option value="1">Jan</option>
                    <option value="2">Feb</option>
                    <option value="3">Mar</option>
                    <option value="4">Apr</option>
                    <option value="5">May</option>
                    <option value="6">Jun</option>
                    <option value="7">Jul</option>
                    <option value="8">Aug</option>
                    <option value="8">Sep</option>
                    <option value="10">Oct</option>
                    <option value="11">Nov</option>
                    <option value="12">Dec</option>
                </Field>
                <OnChange name="byMonth">
                    {handleByMonthChange}
                </OnChange>
            </div>
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
            <div style={{marginRight: "20px", float: "left"}}>
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
                {renderOnField()}
                <div className="uk-form-controls">
                    {renderByMonthField()}
                    {renderByMonthDayField()}
                </div>
            </div>

            <div className="uk-margin">
                {renderOnTheField()}
                <div className="uk-form-controls">
                    {renderBySetPosField()}
                    {renderByDayField()}
                    <div style={{float: "left", marginRight: "20px", marginTop: "5px"}}>of</div>
                    {renderByMonthField()}
                </div>
            </div>

        </div>
    );

}

export default YearlyFields;
