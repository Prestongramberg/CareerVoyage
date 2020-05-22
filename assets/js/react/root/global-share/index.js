import React from "react";
import ReactDOM from "react-dom";
import { applyMiddleware, compose, createStore } from "redux";
import { Provider } from "react-redux";
import thunk from 'redux-thunk';
import reducers from "./reducers";
import App from "./App";

const globalShares = document.getElementsByClassName("react-global-share");
for( let i = 0; i < globalShares.length; i++) {

    const user = JSON.parse( globalShares[i].getAttribute("data-user") ) || {};
    const message = globalShares[i].getAttribute("data-message");
    const title = globalShares[i].textContent;

    const store = createStore(
        reducers,
        {
            users: [],
        },
        compose(
            applyMiddleware(thunk),
            window.__REDUX_DEVTOOLS_EXTENSION__ ? window.__REDUX_DEVTOOLS_EXTENSION__() : f => f
        )
    );

    const render = () => {
        ReactDOM.render(
            <Provider store={store}>
                <App message={message}
                     title={title}
                     user={user}
                     uniqueId={i}
                />
            </Provider>,
            globalShares[i]
        );
    };
    render();
    store.subscribe(render);
}
