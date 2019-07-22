import React from "react";
import ReactDOM from "react-dom";
import App from "./App";

const company_resources_upload = document.getElementById("company-resources-upload");

if( company_resources_upload ) {

    const resources = JSON.parse( company_resources_upload.getAttribute("data-resources") );

    ReactDOM.render(
        <App resources={resources} />,
        document.getElementById("company-resources-upload")
    );
}