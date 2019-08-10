import React from "react";
import ReactDOM from "react-dom";
import { applyMiddleware, compose, createStore } from "redux";
import { Provider } from "react-redux";
import thunk from 'redux-thunk';
import reducers from "./reducers";
import { getInitialDummyState } from "./init";
import App from "./App";

const dummy = document.getElementById("dummy");

if( dummy ) {

    const store = createStore(
        reducers,
        {
            dummy: getInitialDummyState()
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
            document.getElementById("dummy")
        );
    };
    render();
    store.subscribe(render);
}