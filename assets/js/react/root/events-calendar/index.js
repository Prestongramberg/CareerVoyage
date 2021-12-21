import React from "react";
import ReactDOM from "react-dom";
import {applyMiddleware, compose, createStore} from "redux";
import {Provider} from "react-redux";
import thunk from 'redux-thunk';
import reducers from "./reducers";
import {getInitialCalendarState} from "./init";
import App from "./App";

const event_calendars = document.getElementsByClassName("react-events-calendar");

for (let i = 0; i < event_calendars.length; i++) {
    (function (i) {
        const userId = parseInt(event_calendars[i].getAttribute("data-user-id") || 0);
        const schoolId = parseInt(event_calendars[i].getAttribute("data-school-id") || 0);
        const zipcode = event_calendars[i].getAttribute("data-zipcode");
        const hideFilters = !!event_calendars[i].getAttribute("data-hide-filters");

        const store = createStore(
            reducers,
            {
                calendar: getInitialCalendarState(),
                events: [],
                filters: {
                    industries: [],
                    secondaryIndustries: [],
                    eventTypes: {
                        schoolEvents: [],
                        companyEvents: [],
                        otherEvents: []
                    },
                    startDate: null
                },
                search: {
                    radius: 70,
                    zipcode: zipcode,
                    query: '',
                    searchQuery: '',
                    eventType: null,
                    industry: null,
                    secondaryIndustry: null,
                    refetchEvents: false,
                    schoolId: schoolId,
                    userId: userId
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
                    <App userId={userId} schoolId={schoolId} hideFilters={hideFilters}/>
                </Provider>,
                event_calendars[i]
            );
        };
        render();
        store.subscribe(render)
    })(i);
}
