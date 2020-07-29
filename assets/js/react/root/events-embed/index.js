import React from "react";
import ReactDOM from "react-dom";
import { applyMiddleware, compose, createStore } from "redux";
import { Provider } from "react-redux";
import thunk from 'redux-thunk';
import reducers from "./reducers";
import { getInitialCalendarState } from "./init";
import App from "./App";

const event_calendars = document.getElementsByClassName("react-events-embed");

for( let i = 0; i < event_calendars.length; i++) {
    (function(i){
        const userId = parseInt(event_calendars[i].getAttribute("data-user-id") || 0);
        const schoolId = parseInt(event_calendars[i].getAttribute("data-school-id") || 0);
        const zipcode = event_calendars[i].getAttribute("data-zipcode");

        const store = createStore(
            reducers,
            {
                calendar: getInitialCalendarState(),
                events: [],
                industries: [],
                search: {
                    radius: 70,
                    zipcode: zipcode
                }
            },
            compose(
                applyMiddleware(thunk),
                window.__REDUX_DEVTOOLS_EXTENSION__ ? window.__REDUX_DEVTOOLS_EXTENSION__() : f => f
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
    })(i);
}
