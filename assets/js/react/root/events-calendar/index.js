import React from "react";
import ReactDOM from "react-dom";
import { applyMiddleware, compose, createStore } from "redux";
import { Provider } from "react-redux";
import thunk from 'redux-thunk';
import reducers from "./reducers";
import { getInitialCalendarState } from "./init";
import App from "./App";

const eventsCalendar = document.getElementById("react-events-calendar");

if( eventsCalendar ) {

    const store = createStore(
        reducers,
        {
            calendar: getInitialCalendarState(),
            events: [],
            industries: []
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
            eventsCalendar
        );
    };
    render();
    store.subscribe(render);
}
