import React from "react";
import ReactDOM from "react-dom";
import { applyMiddleware, compose, createStore } from "redux";
import { Provider } from "react-redux";
import thunk from 'redux-thunk';
import reducers from "./reducers";
import App from "./App";

const searchableProfessionalListing = document.getElementById("searchable-professional-listing");

if( searchableProfessionalListing ) {

    const user = JSON.parse( searchableProfessionalListing.getAttribute("data-user") ) || {};
    const zipcode = searchableProfessionalListing.getAttribute("data-zipcode");

    const store = createStore(
        reducers,
        {
            companies: [],
            industries: [],
            professionals: [],
            roles: [],
            search: {
                company: '',
                industry: '',
                query: '',
                loading: true,
                radius: 70,
                role: '',
                secondaryIndustry: '',
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
            document.getElementById("searchable-professional-listing")
        );
    };
    render();
    store.subscribe(render);
}
