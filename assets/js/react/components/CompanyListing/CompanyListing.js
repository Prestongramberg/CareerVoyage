import React, { Component } from "react";
import PropTypes from "prop-types";

class CompanyListing extends Component {

    constructor() {
        super();
        const methods = ["toggleFavorite"];
        methods.forEach(method => (this[method] = this[method].bind(this)));
    }

    render() {
        return (
            <div className="uk-card uk-card-default uk-grid-collapse uk-flex-center uk-margin" data-uk-grid>
                <div className="uk-card-media-left uk-width-1-1 uk-width-medium@m">
                    <div className="company-listing__image uk-height-1-1 uk-flex uk-flex-right uk-flex-bottom uk-background-cover uk-light"
                        style={{backgroundImage: `url(${this.props.image})`, minHeight: '150px'}}>
                        <div className="uk-inline uk-padding-small">
                            <span className="favorite-company" onClick={this.toggleFavorite}>
                                { this.props.isFavorite && <i className="fa fa-heart" aria-hidden="true"></i> }
                                { !this.props.isFavorite && <i style={{ opacity: 0.5 }} className="fa fa-heart" aria-hidden="true"></i> }
                            </span>
                        </div>
                    </div>
                </div>
                <div className="uk-width-1-1 uk-width-expand@m">
                    <div className="uk-card-body">
                        <div className="company-listing__meta">
                            <a href={ window.Routing.generate('company_view', { id: this.props.id }) }>
                                <h4 className="uk-card-title-small uk-heading-divider">{ this.props.name }</h4>
                            </a>
                            <p>{ this.props.description }</p>
                            <div className="uk-grid uk-flex-middle" data-uk-grid>
                                <div className="uk-width-auto">
                                    <div className="company-links">
                                        { this.props.website && <a href={this.props.website} target="_blank" className="uk-icon-button uk-margin-small-right" data-uk-icon="world"></a> }
                                        { this.props.phone && <a href={'tel:' + this.props.phone} className="uk-icon-button uk-margin-small-right" data-uk-icon="receiver"></a> }
                                        { this.props.email && <a href={'mailto:' +this.props.email} className="uk-icon-button uk-margin-small-right" data-uk-icon="mail"></a> }
                                        { this.props.linkedIn && <a href={this.props.linkedIn} target="_blank" className="uk-icon-button uk-margin-small-right" data-uk-icon="linkedin"></a> }
                                    </div>
                                </div>
                                <div className="uk-width-expand uk-visible@m">
                                    <div className="uk-align-right">
                                        <a href={ window.Routing.generate('company_view', { id: this.props.id }) }
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

    toggleFavorite() {
        const companyId = this.props.id;
        this.props.isFavorite ? this.props.companyUnfavorite(companyId) : this.props.companyFavorite(companyId);
    }
}

CompanyListing.propTypes = {
    companyFavorite: PropTypes.func,
    companyUnfavorite: PropTypes.func,
    description: PropTypes.string,
    email: PropTypes.string,
    id: PropTypes.number,
    image: PropTypes.string,
    isFavorite: PropTypes.bool,
    name: PropTypes.string,
    linkedIn: PropTypes.string,
    phone: PropTypes.string,
    website: PropTypes.string
};

CompanyListing.defaultProps = {
    companyFavorite: () => {},
    companyUnfavorite: () => {},
    isFavorite: false
};

export default CompanyListing;
