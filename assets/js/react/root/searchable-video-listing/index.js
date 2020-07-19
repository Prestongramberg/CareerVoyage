import React from "react";
import ReactDOM from "react-dom";
import { applyMiddleware, compose, createStore } from "redux";
import { Provider } from "react-redux";
import thunk from 'redux-thunk';
import reducers from "./reducers";
import App from "./App";

const searchableVideoListing = document.getElementById("searchable-video-listing");

if( searchableVideoListing ) {

    const store = createStore(
        reducers,
        {
            companies: [],
            companyVideoIndustries: [],
            careerVideoIndustries: [],
            companyVideos: [],
            careerVideos: [],
            search: {
                loading: true,
                loadingVideos: true,
                loadingUser: true,
            },
            companyVideoSearch: {
                company: '',
                industry: 0,
                query: ''
            },
            careerVideoSearch: {
                company: '',
                industry: 0,
                query: ''
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
            document.getElementById("searchable-video-listing")
        );
    };
    render();
    store.subscribe(render);
}
