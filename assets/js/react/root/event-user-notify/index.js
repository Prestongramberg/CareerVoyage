import React from "react";
import ReactDOM from "react-dom";
import { applyMiddleware, compose, createStore } from "redux";
import { Provider } from "react-redux";
import thunk from 'redux-thunk';
import reducers from "./reducers";
import App from "./App";


document.querySelectorAll('.event-users-notify').forEach(function(eventNotificationContainer) {

    const user = JSON.parse( eventNotificationContainer.getAttribute("data-user") ) || {};
    const experienceId = JSON.parse( eventNotificationContainer.getAttribute("data-experienceId") ) || null;
    const title = eventNotificationContainer.getAttribute("data-title") || "";
    const url = eventNotificationContainer.getAttribute("data-url");

    const store = createStore(
        reducers,
        {
            experienceId: experienceId,
            companies: [],
            industries: [],
            users: [],
            search: {
                company: '',
                industry: '',
                query: '',
                loading: true,
                radius: 70,
                secondaryIndustry: ''
            },
            ui: {
                showModal: false,
                title: title
            },
            form: {
                professionals: [],
                customMessage: '',
                url: url
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
                <App user={user}
                     experience={experienceId}
                />
            </Provider>,
            eventNotificationContainer
        );
    };
    render();
    store.subscribe(render);

});

