import React from "react";
import ReactDOM from "react-dom";
import { applyMiddleware, compose, createStore } from "redux";
import { Provider } from "react-redux";
import thunk from 'redux-thunk';
import reducers from "./reducers";
import { getInitialCalendarState } from "./init";
import App from "./App";

const event_lists = document.getElementsByClassName("react-events-list");

for( let i = 0; i < event_lists.length; i++) {
    (function(i){
        const userId = parseInt(event_lists[i].getAttribute("data-user-id") || 0);
        const schoolId = parseInt(event_lists[i].getAttribute("data-school-id") || 0);
        const zipcode = event_lists[i].getAttribute("data-zipcode");

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
                event_lists[i]
            );
        };
        render();
        store.subscribe(render)
    })(i);
}
