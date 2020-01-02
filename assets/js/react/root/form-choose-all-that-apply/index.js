import React from "react";
import ReactDOM from "react-dom";
import App from "./App";

const formChooseAllThatApply = document.getElementsByClassName("react-form-choose-all-that-apply");
for( let i = 0; i < formChooseAllThatApply.length; i++) {

    const fields = formChooseAllThatApply[i].getAttribute("data-fields");
    const disabled = formChooseAllThatApply[i].getAttribute("data-disabled");

    ReactDOM.render(
        <App
            disabled={disabled}
            fields={fields}
        />,
        formChooseAllThatApply[i]
    );
}
