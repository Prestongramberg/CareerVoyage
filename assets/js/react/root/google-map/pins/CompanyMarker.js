import React from 'react';
import CompanyPinMarker from './company.svg';

class CompanyMarker extends React.Component {
    constructor(props) {
        super(props);
    }

    render() {
        return (
            <img src={CompanyPinMarker}
                 style={{position: 'absolute', transform: 'translate(-50%, -100%)'}}
                 height= "55rem"
                 width= "55rem"
                 alt="company map marker"
            />
        )
    }
}

export default CompanyMarker;
