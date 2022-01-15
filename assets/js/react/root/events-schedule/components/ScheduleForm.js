import React, {useCallback} from "react";
import {Form} from 'react-final-form';
import FrequencyField from "./FrequencyField";
import WeeklyFields from "./WeeklyFields";
import MonthlyFields from "./MonthlyFields";
import YearlyFields from "./YearlyFields";
import DailyFields from "./DailyFields";
import StartFields from "./StartFields";
import EndFields from "./EndFields";

const ScheduleForm = ({
                          schedule,
                          onSubmit,
                          changeByDay,
                          changeInterval,
                          changeFreq,
                          changeByMonthDay,
                          changeMonthlyType,
                          changeBySetPos,
                          changeByMonth,
                          changeYearlyType,
                          changeByDayMultiple,
                          changeStartDate,
                          changeStartTime,
                          changeEndTime,
                          changeEndAction,
                          changeCount,
                          changeUntil
                      }) => {


    const submitCallback = useCallback((values, form, complete) => {
        complete();
        onSubmit(values);
    }, []);

    const renderWeeklyFields = () => {
        return (
            <WeeklyFields
                schedule={schedule}
                changeByDayMultiple={changeByDayMultiple}
                changeInterval={changeInterval}
            />
        );
    };

    const renderMonthlyFields = () => {
        return (
            <MonthlyFields
                schedule={schedule}
                changeByDay={changeByDay}
                changeInterval={changeInterval}
                changeByMonthDay={changeByMonthDay}
                changeBySetPos={changeBySetPos}
                changeMonthlyType={changeMonthlyType}
            />
        );
    };

    const renderYearlyFields = () => {
        return (
            <YearlyFields
                schedule={schedule}
                changeByDay={changeByDay}
                changeByMonth={changeByMonth}
                changeByMonthDay={changeByMonthDay}
                changeBySetPos={changeBySetPos}
                changeYearlyType={changeYearlyType}
            />
        );
    };

    const renderDailyFields = () => {
        return (
            <DailyFields
                schedule={schedule}
                changeInterval={changeInterval}
            />
        );
    };

    const renderStartFields = () => {
        return (
            <StartFields
                schedule={schedule}
                changeStartDate={changeStartDate}
                changeStartTime={changeStartTime}
                changeEndTime={changeEndTime}
            />
        );
    };

    const renderFrequencyField = () => {
        return (
            <FrequencyField
                schedule={schedule}
                changeFreq={changeFreq}
            />
        );
    };

    const renderEndFields = () => {
        return (
            <EndFields
                schedule={schedule}
                changeEndAction={changeEndAction}
                changeCount={changeCount}
                changeUntil={changeUntil}
            />
        )
    }

    return (
        <Form
            initialValues={schedule}
            onSubmit={submitCallback}
            render={({handleSubmit}) => (
                <form onSubmit={handleSubmit}>

                    {renderStartFields()}

                    <hr/>

                    {renderFrequencyField()}

                    {schedule.freq === 'YEARLY' && renderYearlyFields()}
                    {schedule.freq === 'MONTHLY' && renderMonthlyFields()}
                    {schedule.freq === 'WEEKLY' && renderWeeklyFields()}
                    {schedule.freq === 'DAILY' && renderDailyFields()}

                    <hr/>

                    {renderEndFields()}

                    <button style={{"position": "absolute", "top": 0, "right": 0}} type="submit" className="uk-button uk-button-primary">Save Schedule</button>

                </form>
            )}
        />

    );
}

export default ScheduleForm;
