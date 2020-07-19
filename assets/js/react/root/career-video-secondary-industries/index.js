import React from "react";
import ReactDOM from "react-dom";
import { applyMiddleware, compose, createStore } from "redux";
import { Provider } from "react-redux";
import thunk from 'redux-thunk';
import reducers from "./reducers";
import { getInitialState, getInitialSubscriptionsState } from "./init";
import App from "./App";


function renderComponents() {

    const careerVideoSecondaryIndustries = document.getElementsByClassName("career-video-secondary-industries");

    for ( let i = 0; i < careerVideoSecondaryIndustries.length; i++ ) {

        const initialIndustrySubscriptions = JSON.parse( careerVideoSecondaryIndustries[i].getAttribute("data-secondary-industries") );
        const fieldName = careerVideoSecondaryIndustries[i].getAttribute("data-field-name");
        const currentTitle = careerVideoSecondaryIndustries[i].getAttribute("data-current-title");
        const existingTitle = careerVideoSecondaryIndustries[i].getAttribute("data-existing-title");
        const removeDomId = careerVideoSecondaryIndustries[i].getAttribute("data-remove-dom-id");



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
                window.__REDUX_DEVTOOLS_EXTENSION__ ? window.__REDUX_DEVTOOLS_EXTENSION__() : f => f
            )
        );

        const render = () => {
            ReactDOM.render(
                <Provider store={store}>
                    <App
                        initialIndustrySubscriptions={initialIndustrySubscriptions}
                        fieldName={fieldName}
                        currentTitle={currentTitle}
                        existingTitle={existingTitle}
                        removeDomId={removeDomId}
                    />
                </Provider>,
                careerVideoSecondaryIndustries[i]
            );
        };
        render();
        store.subscribe(render);

    }

}

renderComponents();