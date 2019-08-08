import React from "react"
import { connect } from "react-redux"
import { loadEvents } from './actions/actionCreators'
import PropTypes from "prop-types";

class App extends React.Component {

    constructor() {
        super();
        // const methods = ["renderCompanyDropdown", "renderIndustryDropdown", "renderRolesDropdown", "renderSecondaryIndustryDropdown", "getRelevantProfessionals"];
        // methods.forEach(method => (this[method] = this[method].bind(this)));
    }

    render() {

        return (
            <div className="uk-container">
                Calendar will go here
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