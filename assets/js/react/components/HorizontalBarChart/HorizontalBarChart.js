import React, { Component } from "react";
import PropTypes from "prop-types";
import {HorizontalBar} from 'react-chartjs-2';

class HorizontalBarChart extends Component {

    render() {

        const colors = [
            '#9b3192',
            '#ea5f89',
            '#f7b7a3',
            '#fff1c9',
            '#2b0b3f',
            '#57167e'
        ];

        const dataSets = [];

        for ( let i = 0; i < this.props.data.length; i++ ) {
            dataSets.push({
                label: this.props.data[i].label,
                backgroundColor: colors[i],
                data: this.props.data[i].data
            });
        }

        const data = {
            labels: this.props.labels,
            datasets: dataSets
        };

        return (
            <HorizontalBar
                data={data}
                options={{
                    scales: {
                        xAxes: [{
                            ticks: {
                                beginAtZero: true
                            }
                        }]
                    }
                }}
            />
        );
    }
}

HorizontalBarChart.propTypes = {
    data: PropTypes.array,
    labels: PropTypes.array
};

HorizontalBarChart.defaultProps = {
    data: [],
    labels: []
};

export default HorizontalBarChart;
