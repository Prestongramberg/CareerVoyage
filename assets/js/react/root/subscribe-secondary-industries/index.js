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
    const fieldName = subscribeSecondaryIndustries.getAttribute("data-field-name");
    const currentTitle = subscribeSecondaryIndustries.getAttribute("data-current-title");
    const existingTitle = subscribeSecondaryIndustries.getAttribute("data-existing-title");
    const removeDomId = subscribeSecondaryIndustries.getAttribute("data-remove-dom-id");
    const userKind = subscribeSecondaryIndustries.getAttribute("data-user-kind");

    const store = createStore(
        reducers,
        {
            subscriptions: {
                data: [],
                search: [],
                subscribed: getInitialSubscriptionsState( initialIndustrySubscriptions )
            },
            uiState: getInitialState()
        },
        compose(
            applyMiddleware(thunk),
            window.__REDUX_DEVTOOLS_EXTENSION__ ? window.__REDUX_DEVTOOLS_EXTENSION__() : f => f
        )
    );

    const render = () => {
        ReactDOM.render(
            <Provider store={store}>
                <App initialIndustrySubscriptions={initialIndustrySubscriptions} fieldName={fieldName} currentTitle={currentTitle} existingTitle={existingTitle} removeDomId={removeDomId} userKind={userKind} />
            </Provider>,
            document.getElementById("subscribe-secondary-industries")
        );
    };
    render();
    store.subscribe(render);
}
