import React, { Component } from 'react';
import PropTypes from "prop-types";
import { addUser, removeUser, searchChattableUsers, sendNotifications } from "./actions/actionCreators";
import {connect} from "react-redux";

const cb = 'global-share'

export class App extends Component {

    constructor(props) {
        super(props);
        // const methods = ["displayFormField", "getText"];
        // methods.forEach(method => (this[method] = this[method].bind(this)));
    }

    render() {

        const uniqueId = this.props.uniqueId || Math.round(Math.random() * 100000);

        return (
            <div className={cb}>
                <button data-uk-toggle={`target: #global-share-${uniqueId}`} type="button"
                        className="uk-button uk-button-primary uk-button-small">
                    {this.props.title}
                </button>
                <div id={`global-share-${uniqueId}`} className="uk-modal-800" data-uk-modal>
                    <div className="uk-modal-dialog uk-modal-body">
                        <h3>Who would you like to share with?</h3>
                        <div className="uk-search uk-search-default uk-width-1-1">
                            <span data-uk-search-icon></span>
                            <input className="uk-search-input" type="search" placeholder="Search..." onChange={this.props.searchChattableUsers} value={this.props.ui.query} />
                        </div>
                        <div className="uk-margin">
                            <div className="uk-grid">
                                <div className="uk-width-1-2">
                                    <div className={`${cb}__heading`}>Sending to Users:</div>
                                    {this.props.ui.users.map((user) => {
                                        return (
                                            <div key={user.id}
                                                className={`${cb}__user-remove`}
                                                onClick={() => { this.props.removeUser( user ) }}>
                                                <div className="uk-grid">
                                                    <div className="uk-width-expand">
                                                        { user.first_name } { user.last_name }
                                                    </div>
                                                    <div className="uk-width-auto">
                                                        <span uk-icon="icon: close"></span>
                                                    </div>
                                                </div>
                                            </div>
                                        )
                                    })}
                                </div>
                                <div className="uk-width-1-2">
                                    <div className={`${cb}__heading`}>Search Found:</div>
                                    <div className={`${cb}__scrollable`}>
                                        {this.props.users.map((user) => {
                                            return (
                                                <div key={user.id}
                                                     className={`${cb}__user-add`}
                                                     onClick={() => { this.props.addUser( user ) }}>
                                                    { user.first_name } { user.last_name }
                                                </div>
                                            )
                                        })}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div className="uk-margin">
                            <button className="uk-button uk-button-primary" onClick={() => { this.props.sendNotifications( this.props.message ) }}>Share to selected users</button>
                        </div>
                    </div>
                </div>
            </div>
        );
    }
}

App.propTypes = {
    message: PropTypes.string,
    user: PropTypes.object
};

App.defaultProps = {
    user: {}
};

export const mapStateToProps = (state = {}) => ({
    ui: state.ui,
    users: state.users
});

export const mapDispatchToProps = dispatch => ({
    addUser: (user) => dispatch(addUser( user )),
    removeUser: (user) => dispatch(removeUser( user )),
    searchChattableUsers: (event) => dispatch(searchChattableUsers( event.target.value )),
    sendNotifications: () => dispatch(sendNotifications()),
});

const ConnectedApp = connect(
    mapStateToProps,
    mapDispatchToProps
)(App);

export default ConnectedApp;
