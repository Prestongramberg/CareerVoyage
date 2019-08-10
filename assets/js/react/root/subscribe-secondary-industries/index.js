import React from "react";
import ReactDOM from "react-dom";
import { applyMiddleware, compose, createStore } from "redux";
import { Provider } from "react-redux";
import thunk from 'redux-thunk';
import reducers from "./reducers";
import { getInitialState, getInitialSubscriptionsState } from "./init";
import App from "./App";

const subscribeSecondaryIndustries = document.getElementById("subscribe-secondary-industries");

if( subscribeSecondaryIndustries ) {

    const initialIndustrySubscriptions = JSON.parse( subscribeSecondaryIndustries.getAttribute("data-secondary-industries") );
    const currentTitle = subscribeSecondaryIndustries.getAttribute("data-current-title");
    const existingTitle = subscribeSecondaryIndustries.getAttribute("data-existing-title");

    const store = createStore(
        reducers,
        {
            subscriptions: {
                data: [],
                subscribed: getInitialSubscriptionsState( initialIndustrySubscriptions )
            },
            uiState: getInitialState()
        },
        compose(
            applyMiddleware(thunk),
            window.devToolsExtension ? window.devToolsExtension() : f => f
        )
    );

    const render = () => {
        ReactDOM.render(
            <Provider store={store}>
                <App initialIndustrySubscriptions={initialIndustrySubscriptions} currentTitle={currentTitle} existingTitle={existingTitle} />
            </Provider>,
            document.getElementById("subscribe-secondary-industries")
        );
    };
    render();
    store.subscribe(render);
}