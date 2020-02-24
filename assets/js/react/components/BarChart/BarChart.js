import React, { Component } from "react";
import PropTypes from "prop-types";
import {Bar} from 'react-chartjs-2';

class BarChart extends Component {

    render() {

        const data = {
            labels: this.props.labels,
            datasets: [
                {
                    label: this.props.label,
                    backgroundColor: 'rgba(255,99,132,0.2)',
                    borderColor: 'rgba(255,99,132,1)',
                    borderWidth: 1,
                    hoverBackgroundColor: 'rgba(255,99,132,0.4)',
                    hoverBorderColor: 'rgba(255,99,132,1)',
                    data: this.props.data
                }
            ]
        };

        return (
            <Bar
                data={data}
                options={{
                    scales: {
                        yAxes: [{
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

BarChart.propTypes = {
    data: PropTypes.array,
    label: PropTypes.string,
    labels: PropTypes.array
};

BarChart.defaultProps = {
    data: [],
    labels: []
};

export default BarChart;
