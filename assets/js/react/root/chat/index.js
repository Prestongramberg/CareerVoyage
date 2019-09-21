import React from "react";
import ReactDOM from "react-dom";
import { applyMiddleware, compose, createStore } from "redux";
import { Provider } from "react-redux";
import thunk from 'redux-thunk';
import reducers from "./reducers";
import { getInitialChatState, getInitialUiState } from "./init";
import App from "./App";

const chat = document.getElementById("react-chat");

if( chat ) {

    const unreadMessages = parseInt(chat.getAttribute("data-unread-messages"));

    const store = createStore(
        reducers,
        {
            chat: getInitialChatState(),
            ui: getInitialUiState( unreadMessages )
        },
        compose(
            applyMiddleware(thunk),
            window.devToolsExtension ? window.devToolsExtension() : f => f
        )
    );

    const render = () => {
        ReactDOM.render(
            <Provider store={store}>
                <App />
            </Provider>,
            chat
        );
    };
    render();
    store.subscribe(render);
}
