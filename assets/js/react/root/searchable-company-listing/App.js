import React from "react"
import { connect } from "react-redux"
import { companyFavorited, companyUnfavorited, loadCompanies, loadUser, radiusChanged, updateIndustryQuery, updateSearchQuery, zipcodeChanged } from './actions/actionCreators'
import PropTypes from "prop-types";
import CompanyListing from "../../components/CompanyListing/CompanyListing";
import Loader from "../../components/Loader/Loader";

class App extends React.Component {

    constructor() {
        super();
        const methods = ["loadCompanies", "renderIndustryDropdown", "getRelevantCompanies"];
        methods.forEach(method => (this[method] = this[method].bind(this)));
    }

    render() {

        const { user } = this.props;
        const relevantCompanies = this.getRelevantCompanies();
        const favoriteCompanies = this.props.companies.filter(company => company.favorite === true);
        const ranges = [ 25, 50, 70, 150 ];

        return (
            <div className="uk-container">
                <ul className="" data-uk-tab="{connect: '#tab-companies'}" data-uk-switcher>
                    <li className="uk-active"><a href="#all-companies">All Companies</a></li>
                    <li><a href="#favorite-companies">Favorites</a></li>
                    {user && user.roles && user.roles.indexOf("ROLE_ADMIN_USER") === -1 && user.roles.indexOf("ROLE_STUDENT_USER") === -1 && <li><a href="#my-company">My Company</a></li> }
                </ul>

                <div className="uk-switcher" id="tab-companies">
                    <div className="companies__all">
                        <div className="uk-grid-small uk-flex-middle" data-uk-grid>
                            <div className="uk-width-1-1 uk-width-1-1@s uk-width-1-3@l">
                                <div className="uk-search uk-search-default uk-width-1-1">
                                    <span data-uk-search-icon></span>
                                    <input className="uk-search-input" type="search" placeholder="Search by Name..." onChange={this.props.updateSearchQuery} value={this.props.search.query} />
                                </div>
                            </div>
                            { this.renderIndustryDropdown() }
                        </div>

                        <div className="uk-grid-small uk-flex-middle" data-uk-grid>
                            <div className="uk-width-1-1 uk-width-1-1@s uk-width-1-3@l">
                                <div className="uk-search uk-search-default uk-width-1-1">
                                    <span data-uk-search-icon></span>
                                    <input className="uk-search-input" type="search" placeholder="Enter Zip Code..." onChange={(e) => { this.props.zipcodeChanged( e.target.value ) }} value={ this.props.search.zipcode } />
                                </div>
                            </div>
                            <div className="uk-width-1-1 uk-width-1-1@s uk-width-1-3@l">
                                <select className="uk-select" onChange={(e) => { this.props.radiusChanged( e.target.value ) }} >
                                    <option value="">Filter by Radius...</option>
                                    {ranges.map( (range, i) => <option key={i} value={range}>{range} miles</option> )}
                                </select>
                            </div>
                            <div className="uk-width-1-1 uk-width-1-1@s uk-width-1-3@l">
                                <div className="uk-button uk-button-primary" onClick={this.loadCompanies}>Apply</div>
                            </div>
                        </div>

                        <div className="uk-grid" data-uk-grid>
                            <div className="uk-width-1-1 company-listings">
                                { this.props.search.loading && (
                                    <div className="uk-width-1-1 uk-align-center">
                                        <Loader />
                                    </div>
                                )}
                                { !this.props.search.loading && relevantCompanies.map(company => (
                                    <CompanyListing
                                        companyFavorited={this.props.companyFavorited}
                                        companyUnfavorited={this.props.companyUnfavorited}
                                        description={company.shortDescription}
                                        directions={(
                                            company.latitude && company.longitude ? `http://maps.google.com/?q=${ company.street },${ company.city },${ company.zipcode }` : ''
                                        )}
                                        email={company.emailAddress}
                                        id={company.id}
                                        image={company.thumbnailImageURL}
                                        isFavorite={company.favorite}
                                        key={company.id}
                                        linkedIn={company.companyLinkedinPage}
                                        facebook={company.companyFacebookPage}
                                        instagram={company.companyInstagramPage}
                                        twitter={company.companyTwitterPage}
                                        name={company.name}
                                        phone={company.phone}
                                        primaryIndustry={company.primaryIndustry}
                                        website={company.website} />
                                ))}
                                { !this.props.search.loading && relevantCompanies.length === 0 && (
                                    <p>No companies match your selection</p>
                                )}
                            </div>
                        </div>
                    </div>
                    <div className="companies_library">
                        { favoriteCompanies.length > 0 && (
                            <div className="uk-width-1-1 company-listings">
                                { favoriteCompanies.map(company => (
                                    <CompanyListing
                                        companyFavorited={this.props.companyFavorited}
                                        companyUnfavorited={this.props.companyUnfavorited}
                                        description={company.shortDescription}
                                        directions={(
                                            company.latitude && company.longitude ? `http://maps.google.com/?q=${ company.street },${ company.city },${ company.zipcode }` : ''
                                        )}
                                        email={company.emailAddress}
                                        id={company.id}
                                        image={company.thumbnailImageURL}
                                        isFavorite={company.favorite}
                                        key={company.id}
                                        linkedIn={company.companyLinkedinPage}
                                        name={company.name}
                                        phone={company.phone}
                                        website={company.website} />
                                ))}
                            </div>
                        )}
                        { favoriteCompanies.length === 0 && (
                            <div className="uk-placeholder uk-text-center uk-width-1-1">
                                <p>You don't have any favorite <i className="fa fa-heart" aria-hidden="true"></i> companies yet.</p>
                            </div>
                        )}
                    </div>
                    {user && user.roles && user.roles.indexOf("ROLE_ADMIN_USER") === -1 && (
                        <div className="companies_mine">
                            { user.company && user.company.id && (
                                <div>
                                    <div className="uk-card">
                                        <div className="uk-grid uk-flex-middle" data-uk-grid>
                                            <div className="uk-width-small">
                                                <img src={ user.company.thumbnailImageURL } alt="" />
                                            </div>
                                            <div className="uk-width-expand">
                                                <h3>{ user.company.name }</h3>
                                                <div>
                                                    <a href={window.Routing.generate('company_view', {'id': user.company.id})}
                                                       className="uk-button uk-button-default uk-button-small">View</a>
                                                    {user.ownedCompany && user.ownedCompany.id === user.company.id && (
                                                        <a href={window.Routing.generate('company_edit', {'id': user.company.id})}
                                                           className="uk-button uk-button-primary uk-button-small uk-margin-small-left">Edit</a>
                                                    )}

                                                    { !user.ownedCompany && ([
                                                        <button data-uk-toggle="target: #remove-from-company" type="button"
                                                                className="uk-button uk-button-secondary uk-button-small uk-margin-small-left">Remove</button>,
                                                        <div id="remove-from-company" data-uk-modal>
                                                            <div className="uk-modal-dialog uk-modal-body">
                                                                <h2 className="uk-modal-title">Are you sure you want to remove yourself
                                                                    from "{ this.props.user.company.name }"?</h2>
                                                                <div className="uk-margin">
                                                                    <form className="uk-inline uk-margin-right" method="post" action={window.Routing.generate('company_remove_user', { id: user.id })}>
                                                                        <button className="uk-button uk-button-danger" type="submit">Yes</button>
                                                                    </form>
                                                                    <button className="uk-button uk-button-default uk-modal-close">No,
                                                                        Cancel
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    ])}

                                                    { user.ownedCompany && user.ownedCompany.id === user.company.id && ([
                                                        <button data-uk-toggle="target: #remove-company" type="button"
                                                                className="uk-button uk-button-danger uk-button-small uk-margin-small-left">Delete
                                                        </button>,
                                                        <div id="remove-company" data-uk-modal>
                                                            <div className="uk-modal-dialog uk-modal-body">
                                                                <h2 className="uk-modal-title">Are you sure you want to delete company "{ user.company.name }"?</h2>
                                                                <form className="uk-inline uk-margin-right" method="post" action={ window.Routing.generate('company_delete', { id: user.company.id }) }>
                                                                    <button className="uk-button uk-button-danger" type="submit">Yes</button>
                                                                </form>
                                                                <button className="uk-button uk-button-default uk-modal-close">No, Cancel</button>
                                                            </div>
                                                        </div>
                                                    ])}

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            )}
                            { this.props.user && !this.props.user.company && (
                                <div className="uk-placeholder uk-text-center">
                                    <p>You aren't associated with a company yet.</p>
                                    <a href={ window.Routing.generate("company_new") } className="uk-button uk-button-primary uk-button-small">Create a Company</a>
                                </div>
                            )}
                        </div>
                    )}
                </div>
            </div>
        )
    }

    renderIndustryDropdown() {

        if ( this.props.industries.length > 0 ) {
            return <div className="uk-width-1-1 uk-width-1-2@s uk-width-1-3@l">
                <div className="uk-width-1-1 uk-text-truncate" data-uk-form-custom="target: > * > span:first-child">
                    <select onChange={this.props.updateIndustryQuery}>
                        <option value="">Filter by Industry...</option>
                        { this.props.industries.map( industry => <option key={industry.id} value={industry.id}>{industry.name}</option> ) }
                    </select>
                    <button className="uk-button uk-button-default uk-width-1-1 uk-width-autom@l" type="button"
                            tabIndex="-1">
                        <span></span>
                        <span data-uk-icon="icon: chevron-down"></span>
                    </button>
                </div>
            </div>
        }

        return null;
    }

    getRelevantCompanies () {

        return this.props.companies.filter(company => {

            // Set Searchable Fields
            const searchableFields = ["name", "shortDescription"];

            // Filter Category
            if(company.primaryIndustry){
                if ( !!this.props.search.industry && parseInt(company.primaryIndustry.id ) !== parseInt( this.props.search.industry ) ) {
                    return false;
                }
            }

            // Filter By Search Term
            if( this.props.search.query ) {
                return searchableFields.some((field) => company[field] && company[field].toLowerCase().indexOf(this.props.search.query.toLowerCase() ) > -1 );
            }

            return true;
        })

    }

    componentDidMount() {
        this.loadCompanies();
        this.props.loadUser( window.Routing.generate('logged_in_user') );
    }

    loadCompanies() {
        this.props.loadCompanies( window.Routing.generate('get_companies_by_radius', {
            'radius': this.props.search.radius,
            'zipcode': this.props.search.zipcode
        }) );
    }
}

App.propTypes = {
    companies: PropTypes.array,
    industries: PropTypes.array,
    search: PropTypes.object,
    user: PropTypes.object
};

App.defaultProps = {
    companies: [],
    industries: [],
    search: {},
    user: {}
};

export const mapStateToProps = (state = {}) => ({
    companies: state.companies,
    industries: state.industries,
    search: state.search,
    user: state.user
});

export const mapDispatchToProps = dispatch => ({
    companyFavorited: (companyId) => dispatch(companyFavorited(companyId)),
    companyUnfavorited: (companyId) => dispatch(companyUnfavorited(companyId)),
    loadCompanies: (url) => dispatch(loadCompanies(url)),
    loadUser: (url) => dispatch(loadUser(url)),
    radiusChanged: (radius) => dispatch(radiusChanged(radius)),
    updateIndustryQuery: (event) => dispatch(updateIndustryQuery(event.target.value)),
    updateSearchQuery: (event) => dispatch(updateSearchQuery(event.target.value)),
    zipcodeChanged: (zipcode) => dispatch(zipcodeChanged(zipcode)),
});

const ConnectedApp = connect(
    mapStateToProps,
    mapDispatchToProps
)(App);

export default ConnectedApp;
