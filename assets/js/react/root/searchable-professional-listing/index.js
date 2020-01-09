import React from "react";
import ReactDOM from "react-dom";
import { applyMiddleware, compose, createStore } from "redux";
import { Provider } from "react-redux";
import thunk from 'redux-thunk';
import reducers from "./reducers";
import { getInitialSearchState } from "./init";
import App from "./App";

const searchableProfessionalListing = document.getElementById("searchable-professional-listing");

if( searchableProfessionalListing ) {

    const user = JSON.parse( searchableProfessionalListing.getAttribute("data-user") ) || {};

    const store = createStore(
        reducers,
        {
            companies: [],
            industries: [],
            professionals: [],
            roles: [],
            search: getInitialSearchState()
        },
        compose(
            applyMiddleware(thunk),
            window.__REDUX_DEVTOOLS_EXTENSION__ ? window.__REDUX_DEVTOOLS_EXTENSION__() : f => f
        )
    );

    const render = () => {
        ReactDOM.render(
            <Provider store={store}>
                <App user={user} />
            </Provider>,
            document.getElementById("searchable-professional-listing")
        );
    };
    render();
    store.subscribe(render);
}
