import React from "react"
import { connect } from "react-redux"
import { loadChatHistory, openChat } from './actions/actionCreators'
import PropTypes from "prop-types";

const cb = "live-chat";

class App extends React.Component {

    constructor() {
        super();
        const methods = ["renderChatWindow"];
        methods.forEach(method => (this[method] = this[method].bind(this)));
    }

    render() {

        const { ui } = this.props
        const { isChatOpen, unreadMessages } = ui
        const chatWindowClassName = isChatOpen ? `${cb}__window-active` : "";

        return (
            <div className={`${cb}`}>
                <div className={`${cb}__window ${chatWindowClassName}`}>
                    { isChatOpen ? this.renderChatWindow() : null }
                </div>
                <div className={`${cb}__bar`} onClick={ this.openChatWindow }>
                    <div className={`${cb}__bar-unreadMessages`}>
                        { unreadMessages }
                    </div>
                    <div className={`${cb}__bar-text`}>
                        Live Chat
                    </div>
                </div>
            </div>
        )
    }

    renderChatWindow() {

        const { ui } = this.props
        const { isChatLoading, isThreadOpen } = ui

        if( isThreadOpen ) {
            return this.renderThread()
        }

        if ( isChatLoading ) {
            return <p>Loading Chat History....</p>
        }

        return <p>Chat History here...</p>
    }

    renderThread() {

        const { ui } = this.props
        const { isThreadLoading } = ui

        if ( isThreadLoading ) {
            return <p>Loading Thread....</p>
        }

        return <p>Thread here...</p>
    }

    openChatWindow() {
        this.props.openChat()
        this.props.loadChatHistory()
    }

    componentDidMount() {
        // if ( this.props.schoolId ) {
        //     this.props.loadEvents( window.Routing.generate('get_school_experiences', { 'id': this.props.schoolId }) );
        // }
    }
}

App.propTypes = {
    chat: PropTypes.object,
    ui: PropTypes.object
};

App.defaultProps = {
    chat: {},
    ui: {}
};

export const mapStateToProps = (state = {}) => ({
    chats: state.chats,
    ui: state.ui
});

export const mapDispatchToProps = dispatch => ({
    loadChatHistory: () => dispatch(loadChatHistory()),
    openChat: () => dispatch(openChat())
});

const ConnectedApp = connect(
    mapStateToProps,
    mapDispatchToProps
)(App);

export default ConnectedApp;
