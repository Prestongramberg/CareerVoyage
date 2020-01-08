import React from "react";
import ReactDOM from "react-dom";
import App from "./App";

const formRange = document.getElementsByClassName("react-form-range");
for( let i = 0; i < formRange.length; i++) {

    const fieldName = formRange[i].getAttribute("data-name");
    const fieldValue = formRange[i].getAttribute("data-value");
    const disabled = formRange[i].getAttribute("data-disabled");

    ReactDOM.render(
        <App
            disabled={disabled}
            fieldName={fieldName}
            fieldValue={fieldValue}
        />,
        formRange[i]
    );
}
