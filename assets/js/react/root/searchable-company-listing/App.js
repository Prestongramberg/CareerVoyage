import React from "react"
import { connect } from "react-redux"
import { loadCompanies, loadIndustries, updateIndustryQuery, updateSearchQuery } from './actions/actionCreators'
import api from '../../utilities/api/api'
import PropTypes from "prop-types";
import CompanyListing from "../../components/CompanyListing/CompanyListing";

class App extends React.Component {

    constructor() {
        super();
        const methods = ["renderIndustryDropdown", "getRelevantCompanies"];
        methods.forEach(method => (this[method] = this[method].bind(this)));
    }

    render() {

        const relevantCompanies = this.getRelevantCompanies();

        return (
            <div>
                <div className="uk-grid-small uk-flex-middle" data-uk-grid>
                    <div className="uk-width-1-1 uk-width-1-1@s uk-width-1-3@l">
                        <form className="uk-search uk-search-default uk-width-1-1">
                            <span data-uk-search-icon></span>
                            <input className="uk-search-input" type="search" placeholder="Search by Name..." onChange={this.props.updateSearchQuery} value={this.props.search.query} />
                        </form>
                    </div>
                    { this.renderIndustryDropdown() }
                </div>

                <div className="uk-grid" data-uk-grid>
                    <div className="uk-width-1-1 company-listings">
                        { this.props.search.loading && (
                            <div className="uk-width-1-1 uk-align-center">
                                <div data-uk-spinner></div>
                            </div>
                        )}
                        { !this.props.search.loading && relevantCompanies.map(company => (
                            <CompanyListing
                                key={company.id}
                                id={company.id}
                                name={company.name}
                                description={company.shortDescription}
                                website={company.website}
                                phone={company.phone}
                                email={company.emailAddress}
                                linkedIn={company.companyLinkedinPage} />
                        ))}
                        { !this.props.search.loading && relevantCompanies.length === 0 && (
                            <p>No results match your selection</p>
                        )}
                    </div>
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
            if ( !!this.props.search.industry && parseInt(company.primaryIndustry.id ) !== parseInt( this.props.search.industry ) ) {
                return false;
            }

            // Filter By Search Term
            if( this.props.search.query ) {
                return searchableFields.some((field) => company[field].toLowerCase().indexOf(this.props.search.query.toLowerCase() ) > -1 );
            }

            return true;
        })

    }

    componentDidMount() {
        this.props.loadCompanies( window.Routing.generate('get_companies') );
        this.props.loadIndustries( window.Routing.generate('get_industries') );
    }
}

App.propTypes = {
    search: PropTypes.object,
    companies: PropTypes.array
};

App.defaultProps = {
    search: {},
    companies: [],
};

export const mapStateToProps = (state = {}) => ({
    companies: state.companies,
    industries: state.industries,
    search: state.search
});

export const mapDispatchToProps = dispatch => ({
    loadCompanies: (url) => dispatch(loadCompanies(url)),
    loadIndustries: (url) => dispatch(loadIndustries(url)),
    updateIndustryQuery: (event) => dispatch(updateIndustryQuery(event.target.value)),
    updateSearchQuery: (event) => dispatch(updateSearchQuery(event.target.value)),
});

const ConnectedApp = connect(
    mapStateToProps,
    mapDispatchToProps
)(App);

export default ConnectedApp;