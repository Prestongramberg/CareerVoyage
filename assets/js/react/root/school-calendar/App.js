import React from "react"
import { connect } from "react-redux"
import { loadEvents } from './actions/actionCreators'
import PropTypes from "prop-types";
import FullCalendar from '@fullcalendar/react'
import dayGridPlugin from '@fullcalendar/daygrid'

class App extends React.Component {

    constructor() {
        super();
        const methods = ["renderCalendar", "getRelevantEvents"];
        methods.forEach(method => (this[method] = this[method].bind(this)));
    }

    render() {

        if ( ! this.props.schoolId ) {
            return null;
        }

        return this.props.calendar.loading ? (
            <div className="uk-width-1-1 uk-align-center">
                <div data-uk-spinner></div>
            </div>
        ) : this.renderCalendar();
    }

    renderCalendar() {

        const events = this.getRelevantEvents();
        const calendarEvents = events.map(event => {
            return {
                title: event.title,
                start: event.startDateAndTime,
                end: event.endDateAndTime,
                url: window.Routing.generate('school_experience_view', {'id': event.id})
            }
        });

        return (
            <div className="pintex-calendar">
                <FullCalendar
                    defaultView="dayGridMonth"
                    timeZone={'America/Chicago'}
                    events={calendarEvents}
                    header={{
                        left: 'prev,next',
                        center: 'title',
                        right: 'dayGridDay,dayGridWeek,dayGridMonth'
                    }}
                    plugins={[dayGridPlugin]}/>
            </div>
        );
    }

    getRelevantEvents() {
        return this.props.events;
    }


    componentDidMount() {
        if ( this.props.schoolId ) {
            this.props.loadEvents( window.Routing.generate('get_school_experiences', { 'id': this.props.schoolId }) );
        }
    }
}

App.propTypes = {
    calendar: PropTypes.object,
    events: PropTypes.array,
    schoolId: PropTypes.number
};

App.defaultProps = {
    calendar: {},
    events: [],
    schoolId: 0
};

export const mapStateToProps = (state = {}) => ({
    calendar: state.calendar,
    events: state.events
});

export const mapDispatchToProps = dispatch => ({
    loadEvents: (url) => dispatch(loadEvents(url))
});

const ConnectedApp = connect(
    mapStateToProps,
    mapDispatchToProps
)(App);

export default ConnectedApp;
