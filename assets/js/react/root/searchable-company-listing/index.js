import React from "react";
import ReactDOM from "react-dom";
import { applyMiddleware, compose, createStore } from "redux";
import { Provider } from "react-redux";
import thunk from 'redux-thunk';
import reducers from "./reducers";
import { getInitialCompaniesState, getInitialIndustriesState, getInitialSearchState } from "./init";
import App from "./App";

const searchableCompanyListing = document.getElementById("searchable-company-listing");

if( searchableCompanyListing ) {

    const userId = parseInt( searchableCompanyListing.getAttribute("data-user-id") );

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
                <App userId={userId} />
            </Provider>,
            document.getElementById("searchable-company-listing")
        );
    };
    render();
    store.subscribe(render);
}