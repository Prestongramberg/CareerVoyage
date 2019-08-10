import React from "react"
import { connect } from "react-redux"
import { sampleActionCreator, sampleAsyncActionCreator} from './actions/actionCreators'
import PropTypes from "prop-types";

class App extends React.Component {

    constructor() {
        super();
        // const methods = ["renderIndustryDropdown"];
        // methods.forEach(method => (this[method] = this[method].bind(this)));
    }

    render() {

        return (
            <p>This is a dummy redux starter componenet for Pintex duplication</p>
        )
    }

    componentDidMount() {
        this.props.sampleAsyncActionCreator( window.Routing.generate('get_companies') );
    }
}

App.propTypes = {
    dummy: PropTypes.object
};

App.defaultProps = {
    dummy: {}
};

export const mapStateToProps = (state = {}) => ({
    dummy: state.dummy
});

export const mapDispatchToProps = dispatch => ({
    sampleAsyncActionCreator: (event) => dispatch(sampleAsyncActionCreator(event.target.value)),
});

const ConnectedApp = connect(
    mapStateToProps,
    mapDispatchToProps
)(App);

export default ConnectedApp;