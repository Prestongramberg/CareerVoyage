import React from "react";
import ReactDOM from "react-dom";
import BarChart from "../../components/BarChart/BarChart";

const bar_charts = document.getElementsByClassName("react-bar-chart");
for( let i = 0; i < bar_charts.length; i++) {

    const data = JSON.parse(bar_charts[i].getAttribute("data-set"));
    const dataLabel = bar_charts[i].getAttribute("data-label");
    const dataLabels = JSON.parse(bar_charts[i].getAttribute("data-labels"));

    ReactDOM.render(
        <BarChart
            data={data}
            label={dataLabel}
            labels={dataLabels}
        />,
        bar_charts[i]
    );
}
