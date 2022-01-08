import React from "react";
import ReactDOM from "react-dom";
import {applyMiddleware, compose, createStore, combineReducers} from "redux";
import {Provider} from "react-redux";
import thunk from 'redux-thunk';
import reducers from "./reducers";
import App from "./App";
import { reducer as formReducer } from 'redux-form';
import rruleReducer from "./reducers/rrule";

const eventSchedule = document.getElementById("react-events-schedule");

if (eventSchedule) {

    const userId = parseInt(eventSchedule.getAttribute("data-user-id") || 0);

    const rootReducer = combineReducers({
        form: formReducer,
        rrule: rruleReducer
    });

    const store = createStore(
        rootReducer,
        {
            rrule: {
                frequency: '',
                interval: 1,
                byMonth: null,
                byMonthDay: null,
                byDay: null,
                bySetPos: null,
                until: null,
                count: null,
                toString: ''
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
                <App userId={userId}/>
            </Provider>,
            eventSchedule
        );
    };
    render();
    store.subscribe(render)
}
