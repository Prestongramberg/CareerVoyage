import React from "react"
import { connect } from "react-redux"
import { loadProfessionals, updateCompanyQuery, updatePrimaryIndustryQuery, updateRoleQuery, updateSearchQuery, updateSecondaryIndustryQuery } from './actions/actionCreators'
import PropTypes from "prop-types";
import ProfessionalListing from "../../components/ProfessionalListing/ProfessionalListing";

class App extends React.Component {

    constructor() {
        super();
        const methods = ["renderCompanyDropdown", "renderIndustryDropdown", "renderRolesDropdown", "renderSecondaryIndustryDropdown", "getRelevantProfessionals"];
        methods.forEach(method => (this[method] = this[method].bind(this)));
    }

    render() {

        const relevantProfessionals = this.getRelevantProfessionals();

        return (
            <div className="uk-container">
                <ul className="" data-uk-tab="{connect: '#tab-professionals'}" data-uk-switcher>
                    <li className="uk-active"><a href="#all-professionals">All Professionals</a></li>
                </ul>

                <div className="uk-switcher" id="tab-professionals">
                    <div className="professionals__all">
                        <div className="uk-grid-small uk-flex-middle" data-uk-grid>
                            <div className="uk-width-1-1 uk-width-1-1@s uk-width-1-3@l">
                                <form className="uk-search uk-search-default uk-width-1-1">
                                    <span data-uk-search-icon></span>
                                    <input className="uk-search-input" type="search" placeholder="Search by Name..." onChange={this.props.updateSearchQuery} value={this.props.search.query} />
                                </form>
                            </div>
                            { this.renderCompanyDropdown() }
                            { this.renderRolesDropdown() }
                            { this.renderIndustryDropdown() }
                            { this.props.search.industry && this.renderSecondaryIndustryDropdown() }
                        </div>

                        <div className="professional-listings" data-uk-grid="masonry: true">
                            { this.props.search.loading && (
                                <div className="uk-width-1-1 uk-align-center">
                                    <div data-uk-spinner></div>
                                </div>
                            )}
                            { !this.props.search.loading && relevantProfessionals.map(professional => {

                                const primaryIndustry = professional.primaryIndustry !== null ? professional.primaryIndustry.name : null;
                                const secondaryIndustry = professional.secondaryIndustries.length > 0 ? professional.secondaryIndustries[0].name : null;
                                const companyName = professional.company ? professional.company.name : '';

                                return <div className="uk-width-1-1 uk-width-1-2@l" key={professional.id}>
                                    <ProfessionalListing
                                        briefBio={professional.briefBio}
                                        company={companyName}
                                        email={professional.email}
                                        firstName={professional.firstName}
                                        key={professional.id}
                                        image={professional.profilePhotoImageURL}
                                        lastName={professional.lastName}
                                        linkedIn={professional.linkedinProfile}
                                        phone={professional.phone}
                                        primaryIndustry={primaryIndustry}
                                        secondaryIndustry={secondaryIndustry}
                                    />
                                </div>
                            })}
                            { !this.props.search.loading && relevantProfessionals.length === 0 && (
                                <p>No professionals match your selection</p>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        )
    }

    renderCompanyDropdown() {

        if ( this.props.companies.length > 0 ) {
            return <div className="uk-width-1-1 uk-width-1-2@s uk-width-1-3@l">
                <div className="uk-width-1-1 uk-text-truncate" data-uk-form-custom="target: > * > span:first-child">
                    <select onChange={this.props.updateCompanyQuery}>
                        <option value="">Filter by Company...</option>
                        { this.props.companies.map( company => <option key={company.id} value={company.id}>{company.name}</option> ) }
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

    renderRolesDropdown() {

        if ( this.props.roles.length > 0 ) {
            return <div className="uk-width-1-1 uk-width-1-2@s uk-width-1-3@l">
                <div className="uk-width-1-1 uk-text-truncate" data-uk-form-custom="target: > * > span:first-child">
                    <select onChange={this.props.updateRoleQuery}>
                        <option value="">Filter by Roles...</option>
                        { this.props.roles.map( role => <option key={role.id} value={role.id}>{role.name}</option> ) }
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

    renderIndustryDropdown() {

        if ( this.props.industries.length > 0 ) {
            return <div className="uk-width-1-1 uk-width-1-2@s uk-width-1-3@l">
                <div className="uk-width-1-1 uk-text-truncate" data-uk-form-custom="target: > * > span:first-child">
                    <select onChange={this.props.updatePrimaryIndustryQuery}>
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

    renderSecondaryIndustryDropdown() {

        if ( this.props.industries.length > 0 ) {

            const secondaryIndustries = this.props.industries.map( industry => {
                return parseInt(this.props.search.industry ) === parseInt( industry.id ) ? industry.secondaryIndustries : [];
            } ).reduce((a, b) => a.concat(b), []).filter((v,i,a)=>a.findIndex((t)=>(t.id === v.id))===i);

            return <div className="uk-width-1-1 uk-width-1-2@s uk-width-1-3@l">
                <div className="uk-width-1-1 uk-text-truncate" data-uk-form-custom="target: > * > span:first-child">
                    <select onChange={this.props.updateSecondaryIndustryQuery}>
                        <option value="">Filter by Career...</option>
                        { secondaryIndustries.map( industry => <option key={industry.id} value={industry.id}>{industry.name}</option> ) }
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

    getRelevantProfessionals () {

        return this.props.professionals.filter(professional => {

            // Set Searchable Fields
            const searchableFields = ["firstName", "lastName", "briefBio"];

            // Filter By Company
            if ( !!this.props.search.company && professional.company && parseInt(professional.company.id ) !== parseInt( this.props.search.company ) ) {
                return false;
            }

            // Filter By Role
            if ( !!this.props.search.role && professional.rolesWillingToFulfill.filter(role => parseInt( role.id ) === parseInt( this.props.search.role ) ).length === 0 ) {
                return false;
            }

            // Filter By Industry
            if (
                ( !!this.props.search.industry && !professional.primaryIndustry ) ||
                ( !!this.props.search.industry && parseInt(professional.primaryIndustry.id ) !== parseInt( this.props.search.industry ) )
            ) {
                return false;
            }

            // Filter By Sub Industry
            if ( !!this.props.search.secondaryIndustry && professional.secondaryIndustries.filter(secondaryIndustry => parseInt( secondaryIndustry.id ) === parseInt( this.props.search.secondaryIndustry ) ).length === 0 ) {
                return false;
            }

            // Filter By Search Term
            if( this.props.search.query ) {
                return searchableFields.some((field) => professional[field] && professional[field].toLowerCase().indexOf(this.props.search.query.toLowerCase() ) > -1 );
            }

            return true;
        })

    }

    componentDidMount() {
        this.props.loadProfessionals( window.Routing.generate('get_professionals') );
    }
}

App.propTypes = {
    professionals: PropTypes.array,
    search: PropTypes.object,
    userId: PropTypes.number
};

App.defaultProps = {
    companies: [],
    industries: [],
    professionals: [],
    roles: [],
    search: {},
    userId: 0
};

export const mapStateToProps = (state = {}) => ({
    companies: state.companies,
    industries: state.industries,
    professionals: state.professionals,
    roles: state.roles,
    search: state.search
});

export const mapDispatchToProps = dispatch => ({
    loadProfessionals: (url) => dispatch(loadProfessionals(url)),
    updateCompanyQuery: (event) => dispatch(updateCompanyQuery(event.target.value)),
    updatePrimaryIndustryQuery: (event) => dispatch(updatePrimaryIndustryQuery(event.target.value)),
    updateRoleQuery: (event) => dispatch(updateRoleQuery(event.target.value)),
    updateSearchQuery: (event) => dispatch(updateSearchQuery(event.target.value)),
    updateSecondaryIndustryQuery: (event) => dispatch(updateSecondaryIndustryQuery(event.target.value))
});

const ConnectedApp = connect(
    mapStateToProps,
    mapDispatchToProps
)(App);

export default ConnectedApp;