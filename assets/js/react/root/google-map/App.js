import React, { Component } from "react";
import PropTypes from "prop-types";
import GoogleMapReact from 'google-map-react';
import CompanyMarker from './pins/CompanyMarker';
import SchoolMarker from './pins/SchoolMarker';

class App extends Component {
    constructor(props) {
        super(props);
        this.state = {
            clickedMarker: null
        };
    }

    render() {

        return (
            // Important! Always set the container height explicitly
            <div style={{ flex: '0 0 35rem', height: '100vh', position: 'sticky', top: '0'}}>
                <div className="" style={{ height: '100%', width: '100%', position: 'absolute', top: '0px', left: '0px' }}>
                    <GoogleMapReact
                        bootstrapURLKeys={{ key: "AIzaSyCKausNOkCzpMeyVhsL20sXmViWvfQ4rXo" }}
                        defaultCenter={{
                            lat: this.props.focalPointLatitude,
                            lng: this.props.focalPointLongitude
                        }}
                        defaultZoom={this.props.zoom}
                        options={this.createMapOptions}
                    >
                        <CompanyMarker
                            lat={this.props.focalPointLatitude}
                            lng={this.props.focalPointLongitude}
                            key={1}
                            additionalInfo={<p>This is hovered content</p>}
                        />
                    </GoogleMapReact>
                </div>
            </div>
        );
    }

    createMapOptions(maps) {
        return {
            zoomControlOptions: {
                position: maps.ControlPosition.RIGHT_CENTER,
                style: maps.ZoomControlStyle.SMALL
            },
            mapTypeControlOptions: {
                position: maps.ControlPosition.TOP_RIGHT
            },
            mapTypeControl: true
        };
    }
}

App.propTypes = {
    focalPointLatitude: PropTypes.number,
    focalPointLongitude: PropTypes.number,
    companies: PropTypes.array,
    schools: PropTypes.array,
    zoom: PropTypes.number
};

App.defaultProps = {
    companies: [],
    schools: [],
    zoom: 14
};

export default App;
