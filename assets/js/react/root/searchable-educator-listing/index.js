import React from "react";
import ReactDOM from "react-dom";
import { applyMiddleware, compose, createStore } from "redux";
import { Provider } from "react-redux";
import thunk from 'redux-thunk';
import reducers from "./reducers";
import App from "./App";

const searchableEducatorListing = document.getElementById("searchable-educator-listing");

if( searchableEducatorListing ) {

    const user = JSON.parse( searchableEducatorListing.getAttribute("data-user") ) || {};
    const zipcode = searchableEducatorListing.getAttribute("data-zipcode");

    const store = createStore(
        reducers,
        {
            schools: [],
            industries: [],
            educators: [],
            search: {
                query: '',
                loading: true,
                radius: 70,
                zipcode: zipcode
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
                <App user={user} />
            </Provider>,
            document.getElementById("searchable-educator-listing")
        );
    };
    render();
    store.subscribe(render);
}
