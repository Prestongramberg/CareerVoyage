import React from "react";
import ReactDOM from "react-dom";
import App from "./App";


const userImport = document.getElementById("user-import");

if (userImport) {

    const userImportUuid = userImport.getAttribute("data-user-import-uuid");

    const render = () => {
        ReactDOM.render(
            <App
                userImportUuid={userImportUuid}
            />,
            userImport
        );
    };
    render();
}
