import React from "react";
import ReactDOM from "react-dom";
import { applyMiddleware, compose, createStore } from "redux";
import { Provider } from "react-redux";
import thunk from 'redux-thunk';
import reducers from "./reducers";
import { getInitialCalendarState } from "./init";
import App from "./App";

const eventsFallCalendar = document.getElementById("react-events-calendar");

if( eventsFallCalendar ) {

    const store = createStore(
        reducers,
        {
            calendar: getInitialCalendarState(),
            events: []
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
            document.getElementById("react-events-calendar")
        );
    };
    render();
    store.subscribe(render);
}