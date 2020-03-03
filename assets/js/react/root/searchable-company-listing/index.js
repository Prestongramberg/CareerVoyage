import React from "react";
import ReactDOM from "react-dom";
import { applyMiddleware, compose, createStore } from "redux";
import { Provider } from "react-redux";
import thunk from 'redux-thunk';
import reducers from "./reducers";
import App from "./App";

const searchableCompanyListing = document.getElementById("searchable-company-listing");

if( searchableCompanyListing ) {

    const zipcode = searchableCompanyListing.getAttribute("data-zipcode");

    const store = createStore(
        reducers,
        {
            companies: [],
            industries: [],
            search: {
                industry: 0,
                query: '',
                loading: true,
                loadingCompanies: true,
                loadingUser: true,
                radius: 70,
                zipcode: zipcode
            },
            user: {}
        },
        compose(
            applyMiddleware(thunk),
            window.__REDUX_DEVTOOLS_EXTENSION__ ? window.__REDUX_DEVTOOLS_EXTENSION__() : f => f
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
