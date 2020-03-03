import React, { Component } from "react";
import PropTypes from "prop-types";
import Gauge from 'react-svg-gauge';

class GaugeChart extends Component {

    render() {

        const { bgColor, height, label, max, min, value, width } = this.props;

        return (
            <Gauge
                color={ bgColor }
                height={ height }
                label={ label }
                max={ max }
                min={ min }
                value={ value }
                width={ width }
            />
        );
    }
}

GaugeChart.propTypes = {
    backgroundColor: PropTypes.string,
    height: PropTypes.number,
    label: PropTypes.string,
    max: PropTypes.number,
    min: PropTypes.number,
    value: PropTypes.number,
    width: PropTypes.number,
};

GaugeChart.defaultProps = {
    backgroundColor: '#edebeb',
    height: 160,
    max: 100,
    min: 0,
    width: 200
};

export default GaugeChart;
