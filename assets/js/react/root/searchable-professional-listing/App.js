import React from "react"
import { connect } from "react-redux"
import { loadProfessionals, radiusChanged, updateCompanyQuery, updatePrimaryIndustryQuery, updateRoleQuery, updateSearchQuery, updateSecondaryIndustryQuery, zipcodeChanged } from './actions/actionCreators'
import PropTypes from "prop-types";
import ProfessionalListing from "../../components/ProfessionalListing/ProfessionalListing";
import Loader from "../../components/Loader/Loader";

class App extends React.Component {

    constructor() {
        super();
        const methods = ["loadProfessionals", "renderCompanyDropdown", "renderIndustryDropdown", "renderRolesDropdown", "renderSecondaryIndustryDropdown", "getRelevantProfessionals"];
        methods.forEach(method => (this[method] = this[method].bind(this)));
    }

    render() {

        const relevantProfessionals = this.getRelevantProfessionals();
        const { user = {} } = this.props;
        const ranges = [ 25, 50, 70, 150 ];

        return (
            <div className="uk-container">
                <ul className="" data-uk-tab="{connect: '#tab-professionals'}" data-uk-switcher>
                    <li className="uk-active"><a href="#all-professionals">All Professionals</a></li>
                </ul>

                <div className="uk-switcher" id="tab-professionals">
                    <div className="professionals__all">
                        <div className="uk-grid-small uk-flex-middle" data-uk-grid>
                            <div className="uk-width-1-1 uk-width-1-1@s uk-width-1-3@l">
                                <div className="uk-search uk-search-default uk-width-1-1">
                                    <span data-uk-search-icon></span>
                                    <input className="uk-search-input" type="search" placeholder="Search by Name or Interests..." onChange={this.props.updateSearchQuery} value={this.props.search.query} />
                                </div>
                            </div>
                            { this.renderCompanyDropdown() }
                            { this.renderRolesDropdown() }
                            { this.renderIndustryDropdown() }
                            { this.props.search.industry && this.renderSecondaryIndustryDropdown() }
                        </div>
                        <div className="uk-grid-small uk-flex-middle" data-uk-grid>
                            <div className="uk-width-1-1 uk-width-1-1@s uk-width-1-3@l">
                                <div className="uk-search uk-search-default uk-width-1-1">
                                    <span data-uk-location-icon></span>
                                    <input className="uk-search-input" type="search" placeholder="Enter Zip Code..." onChange={(e) => { this.props.zipcodeChanged( e.target.value ) }} value={ this.props.search.zipcode } />
                                </div>
                            </div>
                            <div className="uk-width-1-1 uk-width-1-1@s uk-width-1-3@l">
                                <select className="uk-select" onChange={(e) => { this.props.radiusChanged( e.target.value ) }} value={parseInt( this.props.search.radius )}>
                                    {ranges.map( (range, i) => <option key={i} value={range}>{range} miles</option> )}
                                </select>
                            </div>
                            <div className="uk-width-1-1 uk-width-1-1@s uk-width-1-3@l">
                                <div className="uk-button uk-button-primary" onClick={this.loadProfessionals}>Apply</div>
                            </div>
                        </div>

                        <div className="professional-listings" data-uk-grid="masonry: true">
                            { this.props.search.loading && (
                                <div className="uk-width-1-1 uk-align-center">
                                    <Loader />
                                </div>
                            )}
                            { !this.props.search.loading && relevantProfessionals.map(professional => {

                                const primaryIndustry = professional.primaryIndustry !== null ? professional.primaryIndustry.name : null;
                                const secondaryIndustry = professional.secondaryIndustries.length > 0 ? professional.secondaryIndustries[0].name : null;
                                const professionalCompany = professional.company ? professional.company : {};
                                const hiddenAttributes = user.student ? {} : {
                                    email: professional.emailAfterPrivacySettingsApplied,
                                    linkedIn: professional.linkedinProfile,
                                    phone: professional.phoneAfterPrivacySettingsApplied
                                };

                                return <div className="uk-width-1-1 uk-width-1-2@l" key={professional.id}>
                                    <ProfessionalListing
                                        briefBio={professional.briefBio}
                                        company={professionalCompany}
                                        firstName={professional.firstName}
                                        key={professional.id}
                                        id={professional.id}
                                        image={professional.photoImageURL}
                                        lastName={professional.lastName}
                                        primaryIndustry={primaryIndustry}
                                        secondaryIndustry={secondaryIndustry}
                                        { ...hiddenAttributes }
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
            const searchableFields = ["firstName", "lastName", "briefBio", "interests"];

            // Filter By Company
            if ( !!this.props.search.company && (
                ( !professional.company ) ||
                ( professional.company && parseInt(professional.company.id ) !== parseInt( this.props.search.company ) )
            ) ) {
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
                // basic search fields
                const basicSearchFieldsFound = searchableFields.some((field) => ( professional[field] && professional[field].toLowerCase().indexOf(this.props.search.query.toLowerCase() ) > -1 ) )

                // company name field
                const companyNameFound = professional['company'] && professional['company']['name'].toLowerCase().indexOf(this.props.search.query.toLowerCase() ) > -1

                return basicSearchFieldsFound || companyNameFound
            }

            return true;
        })

    }

    componentDidMount() {
        this.loadProfessionals();
    }

    loadProfessionals() {
        this.props.loadProfessionals( window.Routing.generate('get_professionals_by_radius', {
            'radius': this.props.search.radius,
            'zipcode': this.props.search.zipcode
        }) );
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
    radiusChanged: (radius) => dispatch(radiusChanged(radius)),
    updateCompanyQuery: (event) => dispatch(updateCompanyQuery(event.target.value)),
    updatePrimaryIndustryQuery: (event) => dispatch(updatePrimaryIndustryQuery(event.target.value)),
    updateRoleQuery: (event) => dispatch(updateRoleQuery(event.target.value)),
    updateSearchQuery: (event) => dispatch(updateSearchQuery(event.target.value)),
    updateSecondaryIndustryQuery: (event) => dispatch(updateSecondaryIndustryQuery(event.target.value)),
    zipcodeChanged: (zipcode) => dispatch(zipcodeChanged(zipcode)),
});

const ConnectedApp = connect(
    mapStateToProps,
    mapDispatchToProps
)(App);

export default ConnectedApp;
