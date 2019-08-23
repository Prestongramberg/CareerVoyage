import React from "react";
import ReactDOM from "react-dom";
import { applyMiddleware, compose, createStore } from "redux";
import { Provider } from "react-redux";
import thunk from 'redux-thunk';
import reducers from "./reducers";
import { getInitialCalendarState } from "./init";
import App from "./App";

const school_calendars = document.getElementsByClassName("react-school-calendar");

for( let i = 0; i < school_calendars.length; i++) {

    const schoolId = parseInt(school_calendars[i].getAttribute("data-school-id"));

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
                <App schoolId={ schoolId } />
            </Provider>,
            school_calendars[i]
        );
    };
    render();
    store.subscribe(render);
}
