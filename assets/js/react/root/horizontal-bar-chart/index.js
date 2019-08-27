import React from "react";
import ReactDOM from "react-dom";
import HorizontalBarChart from "../../components/HorizontalBarChart/HorizontalBarChart";

const horizontal_bar_charts = document.getElementsByClassName("react-horizontal-bar-chart");
for( let i = 0; i < horizontal_bar_charts.length; i++) {

    const dataSets = JSON.parse(horizontal_bar_charts[i].getAttribute("data-sets"));
    const chartLabels = JSON.parse(horizontal_bar_charts[i].getAttribute("data-labels"));

    ReactDOM.render(
        <HorizontalBarChart
            data={dataSets}
            labels={chartLabels}
        />,
        horizontal_bar_charts[i]
    );
}
