import React, { Component } from "react";
import PropTypes from "prop-types";

class ProfessionalListing extends Component {

    constructor() {
        super();
        // const methods = ["toggleFavorite"];
        // methods.forEach(method => (this[method] = this[method].bind(this)));
    }

    render() {
        return (
            <p>Professional Listing</p>
        );
    }
}

ProfessionalListing.propTypes = {

};

ProfessionalListing.defaultProps = {
};

export default ProfessionalListing;
