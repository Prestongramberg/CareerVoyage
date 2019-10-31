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

    const userId = parseInt( searchableProfessionalListing.getAttribute("data-user-id") );

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
                <App userId={userId} />
            </Provider>,
            document.getElementById("searchable-professional-listing")
        );
    };
    render();
    store.subscribe(render);
}
