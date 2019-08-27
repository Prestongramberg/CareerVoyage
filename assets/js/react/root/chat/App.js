import React from "react"
import { connect } from "react-redux"
import { loadChat } from './actions/actionCreators'
import PropTypes from "prop-types";

class App extends React.Component {

    constructor() {
        super();
        const methods = ["renderCalendar", "getRelevantEvents"];
        methods.forEach(method => (this[method] = this[method].bind(this)));
    }

    render() {
        return <p>Chat System</p>
    }

    componentDidMount() {
        // if ( this.props.schoolId ) {
        //     this.props.loadEvents( window.Routing.generate('get_school_experiences', { 'id': this.props.schoolId }) );
        // }
    }
}

App.propTypes = {
    chats: PropTypes.object
};

App.defaultProps = {
    chats: {}
};

export const mapStateToProps = (state = {}) => ({
    chats: state.chats
});

export const mapDispatchToProps = dispatch => ({
    loadChat: (url) => dispatch(loadChat(url))
});

const ConnectedApp = connect(
    mapStateToProps,
    mapDispatchToProps
)(App);

export default ConnectedApp;
