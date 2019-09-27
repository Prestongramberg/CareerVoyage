import React from "react";
import ReactDOM from "react-dom";
import { applyMiddleware, compose, createStore } from "redux";
import { Provider } from "react-redux";
import thunk from 'redux-thunk';
import reducers from "./reducers";
import { getInitialCalendarState } from "./init";
import App from "./App";

const event_calendars = document.getElementsByClassName("react-events-calendar");
for( let i = 0; i < event_calendars.length; i++) {

    const userId = parseInt(event_calendars[i].getAttribute("data-user-id"));
    const schoolId = parseInt(event_calendars[i].getAttribute("data-school-id"));

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
                <App userId={userId} schoolId={schoolId} />
            </Provider>,
            event_calendars[i]
        );
    };
    render();
    store.subscribe(render)
}
