import React from "react";
import ReactDOM from "react-dom";
import PieChart from "../../components/PieChart/PieChart";

const pie_charts = document.getElementsByClassName("react-pie-chart");
for( let i = 0; i < pie_charts.length; i++) {

    const data = JSON.parse(pie_charts[i].getAttribute("data-set"));
    const dataLabels = JSON.parse(pie_charts[i].getAttribute("data-labels"));

    ReactDOM.render(
        <PieChart
            data={data}
            labels={dataLabels}
        />,
        pie_charts[i]
    );
}
