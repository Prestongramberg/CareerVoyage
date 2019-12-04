import React, { Component } from "react";
import PropTypes from "prop-types";

class CalendarButton extends Component {

    render() {

        const { description, endTime, location, startTime, title } = this.props

        return <div dangerouslySetInnerHTML={{ __html: window.Pintex.generateAddToCalendarButton( startTime, endTime, title, description, location ) }}></div>
    }
}

CalendarButton.propTypes = {
    description: PropTypes.string,
    endTime: PropTypes.number,
    location: PropTypes.string,
    startTime: PropTypes.number,
    title: PropTypes.string
};

CalendarButton.defaultProps = {};

export default CalendarButton;
