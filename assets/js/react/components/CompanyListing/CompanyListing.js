import React, { Component } from "react";
import PropTypes from "prop-types";
import FavoriteCompany from "../FavoriteCompany/FavoriteCompany";

class CompanyListing extends Component {

    constructor() {
        super();
    }

    render() {
        return (
            <div className="uk-card uk-card-default uk-grid-collapse uk-flex-left uk-margin" data-uk-grid>
                
                {/* Desktop View */}
                <div className="uk-card-media-left uk-width-1-1 uk-width-medium@m uk-visible@m">
                    <div className="company-listing__image uk-height-medium uk-flex uk-flex-right uk-flex-bottom uk-background-cover uk-light"
                        style={{backgroundImage: `url(${this.props.image})`}}>
                        <div className="uk-inline uk-padding-small">
                            <FavoriteCompany
                                boundByProps={true}
                                id={this.props.id}
                                isFavorited={this.props.isFavorite}
                                companyFavorited={this.props.companyFavorited}
                                companyUnfavorited={this.props.companyUnfavorited}
                            />
                        </div>
                    </div>
                </div>

                {/* Mobile View */}
                <div className="uk-grid-collapse uk-flex-left uk-hidden@m mobile-company-listing" data-uk-grid>
                    <div className="uk-card-media-left uk-width-auto">
                        <div className="company-listing__image uk-width-1-1 uk-width-small uk-height-small uk-height-max-small uk-flex uk-flex-right uk-flex-bottom uk-background-cover uk-light"
                            style={{backgroundImage: `url(${this.props.image})`}}>
                            <div className="uk-inline uk-padding-small">
                                <FavoriteCompany
                                    boundByProps={true}
                                    id={this.props.id}
                                    isFavorited={this.props.isFavorite}
                                    companyFavorited={this.props.companyFavorited}
                                    companyUnfavorited={this.props.companyUnfavorited}
                                />
                            </div>
                        </div>
                    </div>

                    <div className="uk-width-expand">
                        <a href={ window.Routing.generate('company_view', { id: this.props.id }) }>
                            <h4 className="uk-card-title-small">{ this.props.name }</h4>
                        </a>
                    </div>
                </div>

                {/* Both Desktop & Mobile */}
                <div className="uk-width-1-1 uk-width-expand@m">
                    <div className="uk-card-body">
                        <div className="company-listing__meta">
                            <div className="uk-hidden@m">
                                <p style={{borderTop: '1px solid #e5e5e5', paddingTop: '10px'}}>
                                    { this.props.primaryIndustry && <strong> {this.props.primaryIndustry.name } </strong> }
                                    <br />
                                    { this.props.description }
                                </p>
                            </div>
                            <div className="uk-visible@m">
                                <a href={ window.Routing.generate('company_view', { id: this.props.id }) }>
                                    <h4 className="uk-card-title-small uk-heading-divider">{ this.props.name }</h4>
                                </a>
                                <p>
                                    { this.props.primaryIndustry && <strong> {this.props.primaryIndustry.name } </strong> }
                                    <br />
                                    { this.props.description }
                                </p>
                            </div>
                            <div className="uk-grid uk-flex-middle" data-uk-grid>
                                <div className="uk-width-auto">
                                    <div className="company-links">
                                        { this.props.website && <a href={this.props.website} target="_blank" className="uk-icon-button uk-margin-small-right" data-uk-icon="world"></a> }
                                        { this.props.phone && <a href={'tel:' + this.props.phone} className="uk-icon-button uk-margin-small-right" data-uk-icon="receiver"></a> }
                                        { this.props.email && <a href={'mailto:' +this.props.email} className="uk-icon-button uk-margin-small-right" data-uk-icon="mail"></a> }
                                        { this.props.linkedIn && <a href={this.props.linkedIn} target="_blank" className="uk-icon-button uk-margin-small-right" data-uk-icon="linkedin"></a> }
                                        { this.props.facebook && <a href={this.props.facebook} target="_blank" className="uk-icon-button uk-margin-small-right" data-uk-icon="facebook"></a> }
                                        { this.props.instagram && <a href={this.props.instagram} target="_blank" className="uk-icon-button uk-margin-small-right" data-uk-icon="instagram"></a> }
                                        { this.props.twitter && <a href={this.props.twitter} target="_blank" className="uk-icon-button uk-margin-small-right" data-uk-icon="twitter"></a> }
                                        { this.props.directions && <a href={this.props.directions} target="_blank" className="uk-icon-button uk-margin-small-right" data-uk-icon="location"></a> }
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
}

CompanyListing.propTypes = {
    companyFavorited: PropTypes.func,
    companyUnfavorited: PropTypes.func,
    description: PropTypes.string,
    directions: PropTypes.string,
    email: PropTypes.string,
    id: PropTypes.number,
    image: PropTypes.string,
    isFavorite: PropTypes.bool,
    name: PropTypes.string,
    linkedIn: PropTypes.string,
    facebook: PropTypes.string,
    instagram: PropTypes.string,
    twitter: PropTypes.string,
    phone: PropTypes.string,
    primaryIndustry: PropTypes.object,
    website: PropTypes.string
};

CompanyListing.defaultProps = {
    companyFavorited: () => {},
    companyUnfavorited: () => {},
    isFavorite: false,
    primaryIndustry: {}
};

export default CompanyListing;
