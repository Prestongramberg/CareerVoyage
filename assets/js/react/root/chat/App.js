import React from "react"
import { connect } from "react-redux"
import { closeChat, closeThread, handlePusherEvent, initiateChatWithUserId, loadChatHistory, loadThread, openChat, openThread, search, sendMessage, showHistory, showSearch, updateMessage, updateSearch } from './actions/actionCreators'
import Loader from '../../components/Loader/Loader'
import PropTypes from "prop-types";
import Pusher from 'pusher-js';

const cb = "live-chat";

class App extends React.Component {

    constructor() {
        super();
        const methods = ["handleDirectChat", "renderChatHistory", "renderChatSearch", "renderChatWindow", "renderThread", "showHistory", "showUserThread", "toggleChatWindow"];
        methods.forEach(method => (this[method] = this[method].bind(this)));
    }

    componentDidMount() {

        // Pusher.logToConsole = true;

        const socket = new Pusher('3b8e318e4abe6c429446', {
            cluster: 'us2',
            forceTLS: true
        });

        let channel = socket.subscribe('chat-' + this.props.userId);

        channel.bind('send-message', (data) => {
            this.props.handlePusherEvent(data)
        });

        window.addEventListener('live-chat-user', this.handleDirectChat )
    }

    componentDidUpdate() {
        if( this.messagesEnd ) {
            this.messagesEnd.scrollIntoView({ behavior: "smooth" });
        }
    }

    componentWillUnmount() {
        window.removeEventListener('live-chat-user', this.handleDirectChat )
    }

    render() {

        const { chat, ui } = this.props
        const { isChatOpen } = ui
        const chatWindowClassName = isChatOpen ? `${cb}__window--active` : "";

        return (
            <div className={`${cb}`}>
                <div className={`${cb}__window ${chatWindowClassName}`}>
                    { isChatOpen ? this.renderChatWindow() : null }
                </div>
                <div className={`${cb}__bar`} onClick={ this.toggleChatWindow }>
                    <div className={`${cb}__bar-unreadMessages`}>
                        { parseInt(chat.unreadMessages) }
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
                    <ul className="uk-tab">
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
                {this.props.chat.usersHistory.length ? (
                    this.props.chat.usersHistory.map((history) => (
                        <div key={ history.user.id } className={`${cb}__window-chat-thread`} onClick={ () => { this.showUserThread( history.user.id ) } } >
                            <div className={`${cb}__window-chat-thread-image`}>
                                <img src={ window.SETTINGS.LOGGED_IN_USER_PHOTO } />
                            </div>
                            <div className={`${cb}__window-chat-thread-name`}>
                                { history.user.fullName }
                            </div>
                            { history.unread_messages > 0 && (
                                <div className={`${cb}__window-chat-thread-unread`}>{ history.unread_messages }</div>
                            )}
                        </div>
                    ))
                ) : (
                    <div>No chat history. Use search to message someone!</div>
                )}
            </div>
        )
    }

    renderChatSearch() {

        const { chat, ui } = this.props

        return (
            <div className={`${cb}__window-chat-search`}>
                <div className={`${cb}__window-chat-search-users`}>
                    {this.props.chat.foundUsersInSearch.length ? (
                        this.props.chat.foundUsersInSearch.map((user) => (
                            <div key={ user.id } className={`${cb}__window-chat-thread`} onClick={ () => { this.showUserThread( user.id ) } } >
                                <div className={`${cb}__window-chat-thread-image`}>
                                    <img src={ window.SETTINGS.LOGGED_IN_USER_PHOTO } />
                                </div>
                                <div className={`${cb}__window-chat-thread-name`}>
                                    { user.first_name } { user.last_name }
                                </div>
                            </div>
                        ))
                    ) : (
                        <div>No users found. Please modify your search below.</div>
                    )}
                </div>
                <div className={`${cb}__window-chat-search-bar`}>
                    <div className={`${cb}__window-chat-search-bar-input`}>
                        <input className="uk-input uk-form-small"
                               onChange={ this.props.updateSearch }
                               onKeyPress={event => {
                                   if (event.key === 'Enter') {
                                       !ui.isSearching && this.props.search( chat.searchTerm )
                                   }
                               }}
                               placeholder="Search for a User"
                               type="text"
                               value={chat.searchTerm}
                        />
                    </div>
                    <div className={`${cb}__window-chat-search-bar-send`}>
                        { ui.isSearching ? <Loader size="small" /> : (
                            <button className="uk-button uk-button-primary uk-button-small" onClick={ () => { !ui.isSearching && this.props.search( chat.searchTerm ) } }>
                                Search
                            </button>
                        )}
                    </div>
                </div>
            </div>
        )
    }

    renderThread() {

        const { ui, userId, chat } = this.props
        const { isThreadLoading } = ui

        if ( isThreadLoading ) {
            return <Loader />
        }

        return (
            <div className={`${cb}__window-thread`}>
                <div className={`${cb}__window-thread-nav`} onClick={ this.showHistory }>
                    <span className="uk-margin-small-right" uk-icon="chevron-left"></span> Back to History
                </div>
                <div className={`${cb}__window-thread-chatting-with`}>Chatting with: { chat.userEngagedWith.fullName }</div>
                {chat.messages.length ? (
                    <div className={`${cb}__window-thread-messages`}>
                        { chat.messages.map( (message) => {

                            const messageClassNameModifier = message.sentFrom.id === userId ? "to" : "from";

                            return (
                                <div key={message.id} className={`${cb}__window-thread-message ${cb}__window-thread-message--${messageClassNameModifier}`}>
                                    <div className={`${cb}__window-thread-message-text ${cb}__window-thread-message-text--${messageClassNameModifier}`}>
                                        { message.body }
                                    </div>
                                </div>
                            )
                        })}
                        <div style={{ float:"left", clear: "both" }}
                             ref={(el) => { this.messagesEnd = el; }}>
                        </div>
                    </div>
                ) : (
                    <div className={`${cb}__window-thread-messages`}>
                        No message history with this user.
                    </div>
                )}
                <div className={`${cb}__window-thread-form`}>
                    <div className={`${cb}__window-thread-form-input`}>
                        <input className="uk-input uk-form-small"
                               onChange={ this.props.updateMessage }
                               onKeyPress={event => {
                                   if (event.key === 'Enter') {
                                       !ui.isMessageSending && this.props.sendMessage( this.props.chat.currentMessage, this.props.chat.chatId )
                                   }
                               }}
                               placeholder="Type a message"
                               type="text"
                               value={chat.currentMessage}
                        />
                    </div>
                    <div className={`${cb}__window-thread-form-send`}>
                        { ui.isMessageSending ? <Loader size="small" /> : (
                            <button className="uk-button uk-button-primary uk-button-small" onClick={ () => { !ui.isMessageSending && this.props.sendMessage( this.props.chat.currentMessage, this.props.chat.chatId ) } }>
                                Send
                            </button>
                        )}
                    </div>
                </div>
            </div>
        )
    }

    handleDirectChat(e) {
        this.props.initiateChatWithUserId( e.detail.userId );
    }

    showHistory() {
        this.props.closeThread()
        this.props.loadChatHistory()
    }

    showUserThread( userId ) {
        this.props.openThread()
        this.props.loadThread( userId )
    }

    toggleChatWindow() {

        const { ui } = this.props

        if( !ui.isChatOpen ) {
            this.props.openChat()
            this.props.loadChatHistory()
        } else {
            this.props.closeChat()
        }
    }

}

App.propTypes = {
    chat: PropTypes.object,
    ui: PropTypes.object,
    userId: PropTypes.number
};

App.defaultProps = {
    chat: {},
    ui: {}
};

export const mapStateToProps = (state = {}) => ({
    chat: state.chat,
    ui: state.ui
});

export const mapDispatchToProps = dispatch => ({
    loadChatHistory: () => dispatch(loadChatHistory()),
    loadThread: (userId) => dispatch(loadThread(userId)),
    openChat: () => dispatch(openChat()),
    closeChat: () => dispatch(closeChat()),
    openThread: () => dispatch(openThread()),
    closeThread: () => dispatch(closeThread()),
    handlePusherEvent: (data) => dispatch(handlePusherEvent(data)),
    initiateChatWithUserId: (userId) => dispatch(initiateChatWithUserId(userId)),
    search: (searchTerm) => dispatch(search(searchTerm)),
    sendMessage: (message, chatId) => dispatch(sendMessage(message, chatId)),
    showHistory: () => dispatch(showHistory()),
    showSearch: () => dispatch(showSearch()),
    updateMessage: (e) => dispatch(updateMessage(e.target.value)),
    updateSearch: (e) => dispatch(updateSearch(e.target.value))
});

const ConnectedApp = connect(
    mapStateToProps,
    mapDispatchToProps
)(App);

export default ConnectedApp;
