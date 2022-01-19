import React, { Component } from 'react';
import { Map, GoogleApiWrapper, InfoWindow, Marker } from 'google-maps-react';
import CompanyPin from "./pins/company.svg"
import SchoolPin from "./pins/school.svg"

const mapStyles = {
    width: '100%',
    height: '100%'
};

export class MapContainer extends Component {

    constructor(props) {
        super(props);
        const methods = ["onMarkerClick", "onClose"];
        methods.forEach(method => (this[method] = this[method].bind(this)));
        this.state = {
            showingInfoWindow: false,  //Hides or the shows the infoWindow
            activeMarker: {},          //Shows the active marker upon click
            selectedPlace: {},         //Shows the infoWindow to the selected place upon a marker
        };
    }

    render() {

        const { focalPointLatitude, focalPointLongitude, experiences, companies, schools, markerIcon } = this.props

        debugger;

        let markerIconUrl = null;
        if(markerIcon === 'company') {
            markerIconUrl = CompanyPin;
        } else if (markerIcon === 'school') {
            markerIconUrl = SchoolPin;
        } else {
            markerIconUrl = CompanyPin;
        }

        return (
            <div style={{ flex: '0 0 35rem', height: '400px', position: 'sticky', top: '0'}}>
                <div className="" style={{ height: '100%', width: '100%', position: 'absolute', top: '0px', left: '0px' }}>
                    <Map
                        google={this.props.google}
                        zoom={14}
                        style={mapStyles}
                        initialCenter={{
                            lat: focalPointLatitude,
                            lng: focalPointLongitude
                        }}
                        gestureHandling="greedy"
                    >

                        {experiences && experiences.length > 0 && experiences.map(experience => {

                            debugger;

                            const lat = parseFloat(experience.latitude)
                            const lng = parseFloat(experience.longitude)

                            if ( !lat || !lng ) {
                                return null
                            }

                            return (
                                <Marker
                                    onClick={this.onMarkerClick}
                                    position={{lat: lat, lng: lng}}
                                    icon={{
                                        url: markerIconUrl,
                                        anchor: new google.maps.Point(32,32),
                                        scaledSize: new google.maps.Size(64,64)
                                    }}
                                    item={experience}
                                    key={'experience' + experience.id}
                                    type={'experience'}
                                />
                            )
                        })}


                        {companies && companies.length > 0 && companies.map(company => {

                            const lat = parseFloat(company.latitude)
                            const lng = parseFloat(company.longitude)

                            if ( !lat || !lng ) {
                                return null
                            }

                            return (
                                <Marker
                                    onClick={this.onMarkerClick}
                                    position={{lat: lat, lng: lng}}
                                    icon={{
                                        url: CompanyPin,
                                        anchor: new google.maps.Point(32,32),
                                        scaledSize: new google.maps.Size(64,64)
                                    }}
                                    item={company}
                                    key={'company' + company.id}
                                    type={'company'}
                                />
                            )
                        })}

                        {schools && schools.length > 0 && schools.map(school => {

                            const lat = parseFloat(school.latitude)
                            const lng = parseFloat(school.longitude)

                            if ( !lat || !lng ) {
                                return null
                            }

                            return (
                                <Marker
                                    onClick={this.onMarkerClick}
                                    position={{lat: lat, lng: lng}}
                                    icon={{
                                        url: SchoolPin,
                                        anchor: new google.maps.Point(32,32),
                                        scaledSize: new google.maps.Size(64,64)
                                    }}
                                    item={school}
                                    key={'school' + school.id}
                                    type={'school'}
                                >
                                </Marker>
                            )
                        })}

                        <InfoWindow
                            key={'infoWindow'}
                            marker={this.state.activeMarker}
                            visible={this.state.showingInfoWindow}
                            onClose={this.onClose}
                        >
                            <div>
                                {this.state.selectedPlace.type === 'experience' && (
                                    <div className="uk-card uk-card-default uk-card-small">
                                        <div className="uk-card-header">
                                            <h3 className="uk-card-title uk-margin-remove-bottom">{ this.state.selectedPlace.item.title }</h3>
                                            <div>
                                                { this.state.selectedPlace.item.formattedAddress }
                                            </div>
                                        </div>
                                    </div>
                                )}
                                {this.state.selectedPlace.type === 'company' && (
                                    <div className="uk-card uk-card-default uk-card-small">
                                        <div className="uk-card-header">
                                            <h3 className="uk-card-title uk-margin-remove-bottom">{ this.state.selectedPlace.item.name }</h3>
                                            <div>
                                                { this.state.selectedPlace.item.address }
                                            </div>
                                            {this.state.selectedPlace.item.phone && (
                                                <div>
                                                    <a href={`tel:${this.state.selectedPlace.item.phone}`}>{ this.state.selectedPlace.item.phone }</a>
                                                </div>
                                            )}
                                            <p>
                                                <a href={ window.Routing.generate('company_view', {'id': this.state.selectedPlace.item.id}) } className="uk-button uk-button-primary uk-button-small" target="_blank">Learn More</a>
                                            </p>
                                        </div>
                                    </div>
                                )}
                                {this.state.selectedPlace.type === 'school' && (
                                    <div className="uk-card uk-card-default uk-card-small">
                                        <div className="uk-card-header">
                                            <h3 className="uk-card-title uk-margin-remove-bottom">{ this.state.selectedPlace.item.name }</h3>
                                            <div>
                                                { this.state.selectedPlace.item.address }
                                            </div>
                                            {this.state.selectedPlace.item.phone && (
                                                <div>
                                                    <a href={`tel:${this.state.selectedPlace.item.phone}`}>{ this.state.selectedPlace.item.phone }</a>
                                                </div>
                                            )}
                                            <p>
                                                <a href={ window.Routing.generate('school_view', {'id': this.state.selectedPlace.item.id}) } className="uk-button uk-button-primary uk-button-small" target="_blank">Learn More</a>
                                            </p>
                                        </div>
                                    </div>
                                )}
                            </div>
                        </InfoWindow>
                    </Map>
                </div>
            </div>
        );
    }

    onMarkerClick(props, marker, e) {
        this.setState({
            selectedPlace: props,
            activeMarker: marker,
            showingInfoWindow: true
        });
    }

    onClose(props) {
        if (this.state.showingInfoWindow) {
            this.setState({
                showingInfoWindow: false,
                activeMarker: null
            });
        }
    };
}

export default GoogleApiWrapper({
    apiKey: 'AIzaSyArX4YOplL3idyJ18Hu7-95fvstDs9h2K4'
})(MapContainer);
