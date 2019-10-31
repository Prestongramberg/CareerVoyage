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
    const userId = parseInt(chat.getAttribute("data-user-id"));

    const store = createStore(
        reducers,
        {
            chat: getInitialChatState( unreadMessages ),
            ui: getInitialUiState()
        },
        compose(
            applyMiddleware(thunk),
            window.__REDUX_DEVTOOLS_EXTENSION__ ? window.__REDUX_DEVTOOLS_EXTENSION__() : f => f
        )
    );

    const render = () => {
        ReactDOM.render(
            <Provider store={store}>
                <App userId={userId} />
            </Provider>,
            chat
        );
    };
    render();
    store.subscribe(render);
}
