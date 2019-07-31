import React from "react";
import ReactDOM from "react-dom";
import { applyMiddleware, compose, createStore } from "redux";
import { Provider } from "react-redux";
import thunk from 'redux-thunk';
import reducers from "./reducers";
import { getInitialProfessionalsState, getInitialSearchState } from "./init";
import App from "./App";

const searchableProfessionalListing = document.getElementById("searchable-professional-listing");

if( searchableProfessionalListing ) {

    const userId = parseInt( searchableProfessionalListing.getAttribute("data-user-id") );

    const store = createStore(
        reducers,
        {
            professionals: getInitialProfessionalsState(),
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
            document.getElementById("searchable-professional-listing")
        );
    };
    render();
    store.subscribe(render);
}