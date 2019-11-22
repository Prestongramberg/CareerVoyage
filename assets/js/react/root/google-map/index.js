import React from "react";
import ReactDOM from "react-dom";
import App from "./App";

const maps = document.getElementsByClassName("react-google-map");
for( let i = 0; i < maps.length; i++) {

    const focalPointLatitude = parseFloat( maps[i].getAttribute("data-latitude") );
    const focalPointLongitude = parseFloat( maps[i].getAttribute("data-longitude") );
    const companies = JSON.parse(maps[i].getAttribute("data-companies"));
    const schools = JSON.parse(maps[i].getAttribute("data-schools"));

    ReactDOM.render(
        <App
            focalPointLatitude={focalPointLatitude}
            focalPointLongitude={focalPointLongitude}
            companies={companies}
            schools={schools}
        />,
        maps[i]
    );
}
