import React from "react";
import ReactDOM from "react-dom";
import { applyMiddleware, compose, createStore } from "redux";
import { Provider } from "react-redux";
import thunk from 'redux-thunk';
import reducers from "./reducers";
import App from "./App";

const searchableSchoolListing = document.getElementById("searchable-school-listing");

if( searchableSchoolListing ) {

    const zipcode = searchableSchoolListing.getAttribute("data-zipcode");

    const store = createStore(
        reducers,
        {
            schools: [],
            search: {
                query: '',
                loading: true,
                loadingSchools: true,
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
            document.getElementById("searchable-school-listing")
        );
    };
    render();
    store.subscribe(render);
}
