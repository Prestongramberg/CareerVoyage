import React from "react"
import { connect } from "react-redux"
import { loadChatHistory, showHistory, showSearch, toggleChat } from './actions/actionCreators'
import Loader from '../../components/Loader/Loader'
import PropTypes from "prop-types";

const cb = "live-chat";

class App extends React.Component {

    constructor() {
        super();
        const methods = ["renderChatHistory", "renderChatHistory", "renderChatWindow", "renderThread", "toggleChatWindow"];
        methods.forEach(method => (this[method] = this[method].bind(this)));
    }

    render() {

        const { ui } = this.props
        const { isChatOpen, unreadMessages } = ui
        const chatWindowClassName = isChatOpen ? `${cb}__window--active` : "";

        return (
            <div className={`${cb}`}>
                <div className={`${cb}__window ${chatWindowClassName}`}>
                    { isChatOpen ? this.renderChatWindow() : null }
                </div>
                <div className={`${cb}__bar`} onClick={ this.toggleChatWindow }>
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
        const { isChatLoading, isThreadOpen, isSearchOpen } = ui

        if( isThreadOpen ) {
            return this.renderThread()
        }

        if ( isChatLoading ) {
            return <Loader />
        }

        return (
            <div className={`${cb}__window-chat`}>
                <div className={`${cb}__window-chat-nav`}>
                    <ul className="uk-tab" data-uk-tab>
                        <li className={ !isSearchOpen ? "uk-active" : "" } onClick={ this.props.showHistory }><a>History</a></li>
                        <li className={ isSearchOpen ? "uk-active" : "" } onClick={ this.props.showSearch }><a>Search</a></li>
                    </ul>
                </div>
                { isSearchOpen ? this.renderChatSearch() : this.renderChatHistory() }
            </div>
        )
    }

    renderChatHistory() {
        return (
            <div className={`${cb}__window-chat-history`}>
                {this.props.chat.history ? (
                    <div>Chat History here..</div>
                ) : (
                    <div>No chat history. Use Search to message someone!</div>
                )}
            </div>
        )
    }

    renderChatSearch() {
        return (
            <div className={`${cb}__window-chat-search`}>
                Search...
            </div>
        )
    }

    renderThread() {

        const { ui, chat } = this.props
        const { isThreadLoading } = ui

        if ( isThreadLoading ) {
            return <Loader />
        }

        return (
            <div className={`${cb}__window-thread`}>
                {chat.messages ? (
                    <div>Chat Messages here..</div>
                ) : (
                    <div>No message history with this user.</div>
                )}
            </div>
        )
    }

    toggleChatWindow() {
        this.props.toggleChat()
        !this.props.ui.isChatOpen && this.props.loadChatHistory()
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
    toggleChat: () => dispatch(toggleChat()),
    showHistory: () => dispatch(showHistory()),
    showSearch: () => dispatch(showSearch())
});

const ConnectedApp = connect(
    mapStateToProps,
    mapDispatchToProps
)(App);

export default ConnectedApp;
