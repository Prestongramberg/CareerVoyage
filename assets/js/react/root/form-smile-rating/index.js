import React from "react";
import ReactDOM from "react-dom";
import App from "./App";

const formSmileRatings = document.getElementsByClassName("react-form-smile-rating");
for( let i = 0; i < formSmileRatings.length; i++) {

    const fieldName = formSmileRatings[i].getAttribute("data-name");
    const fieldValue = formSmileRatings[i].getAttribute("data-value");
    const disabled = formSmileRatings[i].getAttribute("data-disabled");

    ReactDOM.render(
        <App
            disabled={disabled}
            fieldName={fieldName}
            fieldValue={fieldValue}
        />,
        formSmileRatings[i]
    );
}
