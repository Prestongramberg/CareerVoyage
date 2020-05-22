import React from "react";
import ReactDOM from "react-dom";
import { applyMiddleware, compose, createStore } from "redux";
import { Provider } from "react-redux";
import thunk from 'redux-thunk';
import reducers from "./reducers";
import App from "./App";

const eventUserNotifies = document.getElementsByClassName("event-users-notify");
for ( let i = 0; i < eventUserNotifies.length; i++ ) {

    const user = JSON.parse( eventUserNotifies[i].getAttribute("data-user") ) || {};
    const experienceId = JSON.parse( eventUserNotifies[i].getAttribute("data-experienceId") ) || null;
    const title = eventUserNotifies[i].getAttribute("data-title") || "";
    const url = eventUserNotifies[i].getAttribute("data-url");

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
                     uniqueId={i}
                />
            </Provider>,
            eventUserNotifies[i]
        );
    };
    render();
    store.subscribe(render);

}

