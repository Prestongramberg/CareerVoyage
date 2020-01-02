import React from "react";
import ReactDOM from "react-dom";
import App from "./App";

const formRatings = document.getElementsByClassName("react-form-rating");
for( let i = 0; i < formRatings.length; i++) {

    const fieldName = formRatings[i].getAttribute("data-name");
    const fieldValue = formRatings[i].getAttribute("data-value");
    const disabled = formRatings[i].getAttribute("data-disabled");

    ReactDOM.render(
        <App
            disabled={disabled}
            fieldName={fieldName}
            fieldValue={fieldValue}
        />,
        formRatings[i]
    );
}
