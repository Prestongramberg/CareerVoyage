import React from "react";
import ReactDOM from "react-dom";
import {applyMiddleware, compose, createStore} from "redux";
import {Provider} from "react-redux";
import thunk from 'redux-thunk';
import reducers from "./reducers";
import App from "./App";

const school_selection = document.getElementById("react-schools-select-by-radius");

if (school_selection) {

    const fieldName = school_selection.getAttribute("data-field-name");
    const regionFieldName = school_selection.getAttribute("data-region-field-name") || false;
    const symfonyInitValue = JSON.parse(school_selection.getAttribute("data-field-value"));
    const geoRadiusName = school_selection.getAttribute("data-georadius-name");
    const geoRadiusValue = parseInt(school_selection.getAttribute("data-georadius-value"));
    const geoZipCodeName = school_selection.getAttribute("data-geozipcode-name");
    const geoZipCodeValue = parseInt(school_selection.getAttribute("data-geozipcode-value"));
    let regions = regionFieldName ? getCheckedBoxes(regionFieldName) : [];

    debugger;
    let elements = document.querySelectorAll(`input[name="${regionFieldName}"]`);

    if (elements.length > 0) {

        for (let i = 0; i < elements.length; i++) {
            elements[i].addEventListener('change', function () {

                // get the refreshed region list if we need to
                regions = regionFieldName ? getCheckedBoxes(regionFieldName) : [];

                ReactDOM.unmountComponentAtNode(school_selection);

                const store = createStore(
                    reducers,
                    {
                        search: {
                            fieldName: fieldName,
                            geoRadiusName: geoRadiusName,
                            geoRadiusValue: geoRadiusValue | null,
                            geoZipCodeName: geoZipCodeName,
                            geoZipCodeValue: geoZipCodeValue || '',
                            loading: true,
                            schoolStatus: initialSchoolStatus(),
                            regions: regions
                        },
                        schools: []
                    },
                    compose(
                        applyMiddleware(thunk),
                        window.__REDUX_DEVTOOLS_EXTENSION__ ? window.__REDUX_DEVTOOLS_EXTENSION__() : f => f
                    )
                );

                const render = () => {

                    ReactDOM.render(
                        <Provider store={store}>
                            <App/>
                        </Provider>,
                        school_selection
                    );
                };

                render(store);

                store.subscribe(render);
            });
        }

    }

    debugger;

    const store = createStore(
        reducers,
        {
            search: {
                fieldName: fieldName,
                geoRadiusName: geoRadiusName,
                geoRadiusValue: geoRadiusValue | null,
                geoZipCodeName: geoZipCodeName,
                geoZipCodeValue: geoZipCodeValue || '',
                loading: true,
                schoolStatus: initialSchoolStatus(),
                regions: regions
            },
            schools: []
        },
        compose(
            applyMiddleware(thunk),
            window.__REDUX_DEVTOOLS_EXTENSION__ ? window.__REDUX_DEVTOOLS_EXTENSION__() : f => f
        )
    );

    const render = () => {

        ReactDOM.render(
            <Provider store={store}>
                <App/>
            </Provider>,
            school_selection
        );
    };

    render(store);

    store.subscribe(render);

    function getCheckedBoxes(chkboxName) {
        var checkboxes = document.getElementsByName(chkboxName);
        var values = [];
        for (var i = 0; i < checkboxes.length; i++) {
            if (checkboxes[i].checked) {
                values.push(checkboxes[i].value);
            }
        }
        return values.length > 0 ? values : [];
    }

    function initialSchoolStatus() {

        let schoolStatus = {};

        if (symfonyInitValue.length > 0) {
            for (var i = 0; i < symfonyInitValue.length; i++) {
                schoolStatus[`s${symfonyInitValue[i]}`] = true;
            }
        }

        return schoolStatus;
    }
}

