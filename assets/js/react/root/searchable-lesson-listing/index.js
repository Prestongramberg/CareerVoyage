import React from "react";
import ReactDOM from "react-dom";
import { applyMiddleware, compose, createStore } from "redux";
import { Provider } from "react-redux";
import thunk from 'redux-thunk';
import reducers from "./reducers";
import { getInitialSearchState } from "./init";
import App from "./App";

if( document.getElementById("searchable-lesson-listing") ) {

    const store = createStore(
        reducers,
        {
            courses: [],
            favorites: [],
            lessons: [],
            search: getInitialSearchState(),
            teachables: [],
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
            document.getElementById("searchable-lesson-listing")
        );
    };
    render();
    store.subscribe(render);
}
