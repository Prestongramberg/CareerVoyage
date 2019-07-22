import React from "react";
import ReactDOM from "react-dom";
import { applyMiddleware, compose, createStore } from "redux";
import { Provider } from "react-redux";
import thunk from 'redux-thunk';
import reducers from "./reducers";
import { getInitialCompaniesState, getInitialIndustriesState, getInitialSearchState } from "./init";
import App from "./App";

if( document.getElementById("searchable-company-listing") ) {

    const store = createStore(
        reducers,
        {
            companies: getInitialCompaniesState(),
            industries: getInitialIndustriesState(),
            search: getInitialSearchState()
        },
        compose(
            applyMiddleware(thunk),
            window.devToolsExtension ? window.devToolsExtension() : f => f
        )
    );

    const render = () => {
        ReactDOM.render(
            <Provider store={store}>
                <App />
            </Provider>,
            document.getElementById("searchable-company-listing")
        );
    };
    render();
    store.subscribe(render);
}