import React from "react";
import ReactDOM from "react-dom";
import {applyMiddleware, compose, createStore} from "redux";
import {Provider} from "react-redux";
import thunk from 'redux-thunk';
import reducers from "./reducers";
import App from "./App";
import {reducer as formReducer} from 'redux-form';

const globalShare = document.getElementById("react-global-share");

if (globalShare) {

    const user = JSON.parse(globalShare.getAttribute("data-user")) || {};
    const message = globalShare.getAttribute("data-message") || "";
    const experience = globalShare.getAttribute("data-experience") || null;
    const request = globalShare.getAttribute("data-request") || null;
    let hiddenFilters = globalShare.getAttribute("data-hidden-filters") || "[]";
    let defaultFilters = globalShare.getAttribute("data-default-filters") || "{}";
    const title = globalShare.textContent;
    hiddenFilters = JSON.parse(hiddenFilters);
    defaultFilters = JSON.parse(defaultFilters);

    const store = createStore(
        reducers,
        {
            form: defaultFilters || {},
            filters: {
                activePage: 1
            },
            search: {
                message: message,
                user_messages: {},
                user_modified_messages: {},
                filters: {},
                form: {},
                items: [],
                schema: {
                    properties: {}
                },
                typingTimeout: null,
                pagination: {},
                loading: false,
                currentNotifiedUser: null,
                notifiedUsers: [],
                experience: experience,
                request: request,
                hiddenFilters: hiddenFilters
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
                <App message={message}
                     experience={experience}
                     request={request}
                     title={title}
                     user={user}
                />
            </Provider>,
            globalShare
        );
    };
    render();
    store.subscribe(render);

}