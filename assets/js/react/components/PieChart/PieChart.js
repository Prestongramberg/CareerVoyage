import React, { Component } from "react";
import PropTypes from "prop-types";
import {Pie} from 'react-chartjs-2';

class PieChart extends Component {

    render() {

        const colors = [
            '#9b3192',
            '#ea5f89',
            '#f7b7a3',
            '#fff1c9',
            '#2b0b3f',
            '#57167e'
        ];

        const data = {
            labels: this.props.labels,
            datasets: [{
                data: this.props.data,
                backgroundColor: colors.slice(0, this.props.data.length),
            }]
        };

        return (
            <Pie data={data} />
        );
    }
}

PieChart.propTypes = {
    data: PropTypes.array,
    labels: PropTypes.array
};

PieChart.defaultProps = {
    data: [],
    labels: []
};

export default PieChart;
