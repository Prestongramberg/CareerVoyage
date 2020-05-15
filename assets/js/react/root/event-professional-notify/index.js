import React from "react";
import ReactDOM from "react-dom";
import { applyMiddleware, compose, createStore } from "redux";
import { Provider } from "react-redux";
import thunk from 'redux-thunk';
import reducers from "./reducers";
import App from "./App";

const eventProfessionalNotify = document.getElementById("event-professional-notify");

if(eventProfessionalNotify) {

    debugger;
    const user = JSON.parse( eventProfessionalNotify.getAttribute("data-user") ) || {};
    const experienceId = JSON.parse( eventProfessionalNotify.getAttribute("data-experienceId") ) || null;

    const store = createStore(
        reducers,
        {
            experienceId: experienceId,
            companies: [],
            industries: [],
            professionals: [],
            search: {
                company: '',
                industry: '',
                query: '',
                loading: true,
                radius: 70,
                secondaryIndustry: ''
            },
            ui: {
                showModal: false
            },
            form: {
                professionals: [],
                customMessage: ''
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
            document.getElementById("event-professional-notify")
        );
    };
    render();
    store.subscribe(render);
}
