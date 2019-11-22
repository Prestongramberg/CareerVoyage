import React from 'react';
import SchoolPinMarker from './school.svg';

class MapMarker extends React.Component {
    constructor(props) {
        super(props);
    }

    render() {
        return (
            <img src={SchoolPinMarker}
                 style={{position: 'absolute', transform: 'translate(-50%, -100%)'}}
                 height= "30rem"
                 width= "30rem"
                 alt="school map marker"
            />
        )
    }
}

export default MapMarker;
