import React from "react";
import ReactDOM from "react-dom";
import { applyMiddleware, compose, createStore } from "redux";
import { Provider } from "react-redux";
import thunk from 'redux-thunk';
import reducers from "./reducers";
import App from "./App";

const school_selections = document.getElementsByClassName("react-schools-select-by-radius");
for( let i = 0; i < school_selections.length; i++) {

    debugger;
    const fieldName = school_selections[i].getAttribute("data-field-name");
    const regionFieldName = school_selections[i].getAttribute("data-region-field-name");
    const useRegionsPreselect = (school_selections[i].getAttribute("data-use-regions-preselect") === 'true');
    const symfonyInitValue = JSON.parse( school_selections[i].getAttribute("data-field-value") );
    const geoRadiusName = school_selections[i].getAttribute("data-georadius-name");
    const geoRadiusValue = parseInt( school_selections[i].getAttribute("data-georadius-value") );
    const geoZipCodeName = school_selections[i].getAttribute("data-geozipcode-name");
    const geoZipCodeValue = parseInt( school_selections[i].getAttribute("data-geozipcode-value") );
    // todo refactor this in the near future to be passed in as a data attribute like the vars above
    const useClickHandler = true;

    function initialSchoolStatus() {

        let schoolStatus = {};

        if ( symfonyInitValue.length > 0 ) {
            for ( var i = 0; i < symfonyInitValue.length; i++ ) {
                schoolStatus[ `s${symfonyInitValue[i]}` ] = true;
            }
        }

        return schoolStatus;
    }

    if(useClickHandler && document.getElementById("school-tab")) {

        document.getElementById("school-tab").addEventListener("click", function() {
            debugger;

            if(this.component) {
                ReactDOM.unmountComponentAtNode(school_selections[i]);
            }

            let regions = getCheckedBoxes(regionFieldName);

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

                this.component = ReactDOM.render(
                    <Provider store={store}>
                        <App />
                    </Provider>,
                    school_selections[i]
                );
            };

            debugger;
            render(store);

            store.subscribe(render);
        });


    } else {

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
                    regions: []
                },
                schools: []
            },
            compose(
                applyMiddleware(thunk),
                window.__REDUX_DEVTOOLS_EXTENSION__ ? window.__REDUX_DEVTOOLS_EXTENSION__() : f => f
            )
        );

        const render = () => {

            this.component = ReactDOM.render(
                <Provider store={store}>
                    <App />
                </Provider>,
                school_selections[i]
            );
        };

        render(store);

        store.subscribe(render);
    }

}


function getCheckedBoxes(chkboxName) {
    debugger;
    var checkboxes = document.getElementsByName(chkboxName);
    var values = [];
    // loop over them all
    for (var i=0; i<checkboxes.length; i++) {
        debugger;
        // And stick the checked ones onto an array...
        if (checkboxes[i].checked) {
            debugger;
            values.push(checkboxes[i].value);
        }
    }
    // Return the array if it is non-empty, or null
    return values.length > 0 ? values : [];
}
