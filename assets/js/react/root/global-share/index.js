import React from "react";
import ReactDOM from "react-dom";
import { applyMiddleware, compose, createStore } from "redux";
import { Provider } from "react-redux";
import thunk from 'redux-thunk';
import reducers from "./reducers";
import App from "./App";

const globalShare = document.getElementById("react-global-share");

if(globalShare) {

    debugger;
    const user = JSON.parse( globalShare.getAttribute("data-user") ) || {};
    const message = globalShare.getAttribute("data-message");
    const title = globalShare.textContent;

    const store = createStore(
        reducers,
        {
            users: {
                professionals: [],
                educators: [],
                students: [],
                school_admins: [],
                all: []
            },
            filters: {
                roles: [],
                user_roles: [
                    { label: 'Professional', value: 'professional' },
                    { label: 'Student', value: 'student' },
                    { label: 'Educator', value: 'educator' },
                    { label: 'School Administrator', value: 'school_administrator' },
                    { label: 'Company Administrator', value: 'company_administrator' }
                ],
                companies: [],
                primary_industries: [],
                secondary_industries: [],
                interests: [],
                company_admins: [],
                schools: [],
                courses_taught: [],
            },
            search: {
                roles: [],
                companies: [],
                user_roles: [],
                query: '',
                interests: '',
                company_admins: [],
                schools: [],
                courses_taught: [],
                primary_industries: [],
                secondary_industries: [],
            },
            ui: {
                users: []
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