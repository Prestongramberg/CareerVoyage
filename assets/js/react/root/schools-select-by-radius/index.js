import React from "react";
import ReactDOM from "react-dom";
import { applyMiddleware, compose, createStore } from "redux";
import { Provider } from "react-redux";
import thunk from 'redux-thunk';
import reducers from "./reducers";
import App from "./App";

const school_selections = document.getElementsByClassName("react-schools-select-by-radius");
for( let i = 0; i < school_selections.length; i++) {

    const fieldName = school_selections[i].getAttribute("data-field-name");
    const symfonyInitValue = JSON.parse( school_selections[i].getAttribute("data-field-value") );
    const geoRadiusName = school_selections[i].getAttribute("data-georadius-name");
    const geoRadiusValue = parseInt( school_selections[i].getAttribute("data-georadius-value") );
    const geoZipCodeName = school_selections[i].getAttribute("data-geozipcode-name");
    const geoZipCodeValue = parseInt( school_selections[i].getAttribute("data-geozipcode-value") );

    const store = createStore(
        reducers,
        {
            search: {
                fieldName: fieldName,
                geoRadiusName: geoRadiusName,
                geoRadiusValue: geoRadiusValue || 70,
                geoZipCodeName: geoZipCodeName,
                geoZipCodeValue: geoZipCodeValue || '',
                loading: true,
                schoolStatus: initialSchoolStatus()
            },
            schools: []
        },
        compose(
            applyMiddleware(thunk),
            window.__REDUX_DEVTOOLS_EXTENSION__ ? window.__REDUX_DEVTOOLS_EXTENSION__() : f => f
        )
    );

    function initialSchoolStatus() {

        let schoolStatus = {};

        if ( symfonyInitValue.length > 0 ) {
            for ( var i = 0; i < symfonyInitValue.length; i++ ) {
                schoolStatus[ `s${symfonyInitValue[i]}` ] = true;
            }
        }

        return schoolStatus;
    }

    const render = () => {
        ReactDOM.render(
            <Provider store={store}>
                <App />
            </Provider>,
            school_selections[i]
        );
    };
    render();
    store.subscribe(render);
}
