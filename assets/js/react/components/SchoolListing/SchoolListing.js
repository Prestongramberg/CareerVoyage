import React, { Component } from "react";
import PropTypes from "prop-types";

class SchoolListing extends Component {

    constructor() {
        super();
    }

    render() {
        return (
            <div className="uk-card uk-card-default uk-grid-collapse uk-flex-center uk-margin" data-uk-grid>
                <div className="uk-card-media-left uk-width-1-1 uk-width-medium@m">
                    <div className="school-listing__image uk-height-1-1 uk-flex uk-flex-right uk-flex-bottom uk-background-cover uk-light"
                        style={{backgroundImage: `url(${this.props.image})`, minHeight: '150px'}}>
                    </div>
                </div>
                <div className="uk-width-1-1 uk-width-expand@m">
                    <div className="uk-card-body">
                        <div className="school-listing__meta">
                            <a href={ window.Routing.generate('school_view', { id: this.props.id }) }>
                                <h4 className="uk-card-title-small uk-heading-divider">{ this.props.name }</h4>
                            </a>
                            <p>{ this.props.description }</p>
                            <div className="uk-grid uk-flex-middle" data-uk-grid>
                                <div className="uk-width-auto">
                                    <div className="school-links">
                                        { this.props.website && <a href={this.props.website} target="_blank" className="uk-icon-button uk-margin-small-right" data-uk-icon="world"></a> }
                                        { this.props.phone && <a href={'tel:' + this.props.phone} className="uk-icon-button uk-margin-small-right" data-uk-icon="receiver"></a> }
                                        { this.props.email && <a href={'mailto:' +this.props.email} className="uk-icon-button uk-margin-small-right" data-uk-icon="mail"></a> }
                                        { this.props.linkedIn && <a href={this.props.linkedIn} target="_blank" className="uk-icon-button uk-margin-small-right" data-uk-icon="linkedin"></a> }
                                        { this.props.directions && <a href={this.props.directions} target="_blank" className="uk-icon-button uk-margin-small-right" data-uk-icon="location"></a> }
                                    </div>
                                </div>
                                <div className="uk-width-expand uk-visible@m">
                                    <div className="uk-align-right">
                                        <a href={ window.Routing.generate('school_view', { id: this.props.id }) }
                                           className="uk-button uk-button-small uk-button-text uk-text-muted">More info</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        );
    }
}

SchoolListing.propTypes = {
    description: PropTypes.string,
    directions: PropTypes.string,
    email: PropTypes.string,
    id: PropTypes.number,
    image: PropTypes.string,
    name: PropTypes.string,
    linkedIn: PropTypes.string,
    phone: PropTypes.string,
    website: PropTypes.string
};

SchoolListing.defaultProps = {
};

export default SchoolListing;
