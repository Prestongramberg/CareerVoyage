import React from "react";
import ReactDOM from "react-dom";
import GaugeChart from "../../components/GaugeChart/GaugeChart";

const gauge_chart = document.getElementsByClassName("react-gauge-chart");
for( let i = 0; i < gauge_chart.length; i++) {

    const bgColor = gauge_chart[i].getAttribute("data-bg-color");
    const height = parseInt( gauge_chart[i].getAttribute("data-height") );
    const label = gauge_chart[i].getAttribute("data-label");
    const max = parseFloat( gauge_chart[i].getAttribute("data-max") );
    const min = parseFloat( gauge_chart[i].getAttribute("data-min") );
    const value = parseFloat( gauge_chart[i].getAttribute("data-value") );
    const width = parseInt( gauge_chart[i].getAttribute("data-width") );

    const props = {};

    if ( height ) {
        props.height = height;
    }

    if ( width ) {
        props.width = width;
    }

    ReactDOM.render(
        <GaugeChart
            bgColor={ bgColor }
            label={ label }
            max={ max }
            min={ min }
            value={ value }
            { ...props }
        />,
        gauge_chart[i]
    );
}
