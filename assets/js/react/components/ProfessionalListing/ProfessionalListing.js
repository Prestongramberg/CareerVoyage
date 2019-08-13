import React, { Component } from "react";
import PropTypes from "prop-types";
import { truncate } from "../../utilities/string-utils";

class ProfessionalListing extends Component {

    render() {
        return (
            <div>
                <div className="uk-card uk-card-default">
                    <div className="uk-card-header">
                        <div className="uk-grid-small uk-flex-middle" data-uk-grid>
                            <div className="uk-width-auto">
                                <img className="uk-border-circle" width="40" height="40" src={ this.props.image } />
                            </div>
                            <div className="uk-width-expand">
                                <a href={window.Routing.generate('profile_index', {'id': this.props.id})}>
                                    <h3 className="uk-card-title-small uk-margin-remove-bottom">{ this.props.firstName } {this.props.lastName}</h3>
                                </a>
                                <p className="uk-text-meta uk-margin-remove-top">
                                    { this.props.company ? <span>{ this.props.company }</span> : <span>Professional</span> }
                                    { this.props.primaryIndustry && <span> - { this.props.primaryIndustry }</span> }
                                    { this.props.secondaryIndustry && <span> - { this.props.secondaryIndustry }</span> }
                                </p>
                            </div>
                        </div>
                    </div>
                    <div className="uk-card-body">
                        <div className="uk-margin">
                            { truncate( this.props.briefBio ) || "This professional hasn't added a description yet." }
                        </div>
                        <div className="professional-links">
                            {this.props.email && (
                                <a href={`mailto:${this.props.email}`} className="uk-icon-button uk-margin-small-right" data-uk-icon="mail"></a>
                            )}
                            { this.props.phone && <a href={`tel:${this.props.phone}`} className="uk-icon-button uk-margin-small-right" data-uk-icon="receiver"></a>}
                            { this.props.linkedIn && <a href={this.props.linkedIn} className="uk-icon-button uk-margin-small-right" data-uk-icon="linkedin"  target="_blank"></a>}
                        </div>
                    </div>
                </div>
            </div>
        );
    }
}

ProfessionalListing.propTypes = {
    briefBio: PropTypes.string,
    company: PropTypes.string,
    email: PropTypes.string,
    firstName: PropTypes.string,
    id: PropTypes.number,
    image: PropTypes.string,
    lastName: PropTypes.string,
    linkedIn: PropTypes.string,
    phone: PropTypes.string,
    primaryIndustry: PropTypes.string,
    secondaryIndustry: PropTypes.string
};

ProfessionalListing.defaultProps = {
};

export default ProfessionalListing;
