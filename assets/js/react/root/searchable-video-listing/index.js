import React from "react";
import ReactDOM from "react-dom";
import { applyMiddleware, compose, createStore } from "redux";
import { Provider } from "react-redux";
import thunk from 'redux-thunk';
import reducers from "./reducers";
import App from "./App";
import professionalVideoIndustries from "./reducers/professionalVideoIndustries";

const searchableVideoListing = document.getElementById("searchable-video-listing");

if( searchableVideoListing ) {

    const store = createStore(
        reducers,
        {
            companies: [],
            allVideoIndustries: [],
            companyVideoIndustries: [],
            careerVideoIndustries: [],
            professionalVideoIndustries: [],
            allVideos: [],
            companyVideos: [],
            careerVideos: [],
            professionalVideos: [],
            search: {
                loading: true,
                loadingVideos: true,
                loadingUser: true,
            },
            allVideoSearch: {
                company: '',
                industry: 0,
                query: ''
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
            professionalVideoSearch: {
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
