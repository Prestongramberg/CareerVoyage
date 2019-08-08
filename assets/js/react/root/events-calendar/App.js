import React from "react"
import { connect } from "react-redux"
import { loadEvents } from './actions/actionCreators'
import PropTypes from "prop-types";
import FullCalendar from '@fullcalendar/react'
import dayGridPlugin from '@fullcalendar/daygrid'

class App extends React.Component {

    constructor() {
        super();
        // const methods = ["renderCompanyDropdown", "renderIndustryDropdown", "renderRolesDropdown", "renderSecondaryIndustryDropdown", "getRelevantProfessionals"];
        // methods.forEach(method => (this[method] = this[method].bind(this)));
    }

    render() {

        return (
            <div className="uk-container">
                <FullCalendar
                    defaultView="dayGridMonth"
                    plugins={dayGridPlugin}
                    weekends={false}
                    events={[
                        { title: 'event 1', date: '2019-04-01' },
                        { title: 'event 2', date: '2019-04-02' }
                    ]}
                />
            </div>
        );
    }


    componentDidMount() {
        this.props.loadEvents( window.Routing.generate('get_experiences') );
    }
}

App.propTypes = {
    calendar: PropTypes.object,
    events: PropTypes.array
};

App.defaultProps = {
    calendar: {},
    events: []
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