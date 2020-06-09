import React from "react"
import { connect } from "react-redux"
import { loadEducators, radiusChanged, updateSchoolQuery, updateSearchQuery, zipcodeChanged } from './actions/actionCreators'
import PropTypes from "prop-types";
import EducatorListing from "../../components/EducatorListing/EducatorListing";
import Loader from "../../components/Loader/Loader";
import {
    updatePrimaryIndustryQuery,
    updateRoleQuery,
    updateSecondaryIndustryQuery
} from "../searchable-professional-listing/actions/actionCreators";

class App extends React.Component {

    constructor() {
        super();
        const methods = ["loadEducators", "getRelevantEducators", "renderSchoolDropdown", "renderIndustryDropdown", "renderSecondaryIndustryDropdown"];
        methods.forEach(method => (this[method] = this[method].bind(this)));
    }

    render() {

        const relevantEducators = this.getRelevantEducators();
        const { user = {} } = this.props;
        const ranges = [ 25, 50, 70, 150 ];

        return (
            <div className="uk-container">
                <ul className="" data-uk-tab="{connect: '#tab-educators'}" data-uk-switcher>
                    <li className="uk-active"><a href="#all-educators">All Educators</a></li>
                </ul>

                <div className="uk-switcher" id="tab-educators">
                    <div className="educators__all">
                        <div className="uk-grid-small uk-flex-middle" data-uk-grid>
                            <div className="uk-width-1-1 uk-width-1-1@s uk-width-1-3@l">
                                <div className="uk-search uk-search-default uk-width-1-1">
                                    <span data-uk-search-icon></span>
                                    <input className="uk-search-input" type="search" placeholder="Search by Name or School..." onChange={this.props.updateSearchQuery} value={this.props.search.query} />
                                </div>
                            </div>
                            { this.renderSchoolDropdown() }
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
                                <select className="uk-select" onChange={(e) => { this.props.radiusChanged( e.target.value ) }} >
                                    <option value="">Filter by Radius...</option>
                                    {ranges.map( (range, i) => <option key={i} value={range}>{range} miles</option> )}
                                </select>
                            </div>
                            <div className="uk-width-1-1 uk-width-1-1@s uk-width-1-3@l">
                                <div className="uk-button uk-button-primary" onClick={this.loadEducators}>Apply</div>
                            </div>
                        </div>

                        <div className="educator-listings" data-uk-grid="masonry: true">
                            { this.props.search.loading && (
                                <div className="uk-width-1-1 uk-align-center">
                                    <Loader />
                                </div>
                            )}
                            { !this.props.search.loading && relevantEducators.map(educator => {

                                const hiddenAttributes = user.student ? {} : {
                                    email: !educator.isEmailHiddenFromProfile ? educator.email : null,
                                    linkedIn: educator.linkedinProfile,
                                    phone: !educator.isPhoneHiddenFromProfile ? educator.phone : null
                                };

                                return <div className="uk-width-1-1 uk-width-1-2@l" key={educator.id}>
                                    <EducatorListing
                                        briefBio={educator.briefBio}
                                        firstName={educator.firstName}
                                        key={educator.id}
                                        id={educator.id}
                                        image={educator.photo}
                                        lastName={educator.lastName}
                                        interests={educator.interests}
                                        school={educator.school}
                                        { ...hiddenAttributes }
                                    />
                                </div>
                            })}
                            { !this.props.search.loading && relevantEducators.length === 0 && (
                                <p>No educators match your selection</p>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        )
    }

    getRelevantEducators () {

        return this.props.educators.filter(educator => {
            // Set Searchable Fields
            const searchableFields = ["firstName", "lastName"];

            // Filter By School
            if ( !!this.props.search.school && (
                ( !educator.school ) ||
                ( educator.school && parseInt(educator.school.id ) !== parseInt( this.props.search.school ) )
            ) ) {
                return false;
            }

            // Filter By Industry
            if ( !!this.props.search.industry && educator.secondaryIndustries.filter(secondaryIndustry => parseInt( secondaryIndustry.primaryIndustry.id ) === parseInt( this.props.search.industry ) ).length === 0 ) {
                return false;
            }

            // Filter By Sub Industry
            if ( !!this.props.search.secondaryIndustry && educator.secondaryIndustries.filter(secondaryIndustry => parseInt( secondaryIndustry.id ) === parseInt( this.props.search.secondaryIndustry ) ).length === 0 ) {
                return false;
            }

            // Filter By Search Term
            if( this.props.search.query ) {
                // basic search fields
                const basicSearchFieldsFound = searchableFields.some((field) => ( educator[field] && educator[field].toLowerCase().indexOf(this.props.search.query.toLowerCase() ) > -1 ) )

                // company name field
                const schoolNameFound = educator['school'] && educator['school']['name'].toLowerCase().indexOf(this.props.search.query.toLowerCase() ) > -1

                return basicSearchFieldsFound || schoolNameFound

            }



            return true;
        })

    }

    componentDidMount() {
        this.loadEducators();
    }

    renderSchoolDropdown() {
        if ( this.props.schools.length > 0 ) {
            return <div className="uk-width-1-1 uk-width-1-2@s uk-width-1-3@l">
                <div className="uk-width-1-1 uk-text-truncate" data-uk-form-custom="target: > * > span:first-child">
                    <select onChange={this.props.updateSchoolQuery}>
                        <option value="">Filter by School...</option>
                        { this.props.schools.map( school => <option key={school.id} value={school.id}>{school.name}</option> ) }
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

    loadEducators() {
        this.props.loadEducators( window.Routing.generate('get_educators_by_radius', {
            'radius': this.props.search.radius,
            'zipcode': this.props.search.zipcode
        }) );
    }
}

App.propTypes = {
    educators: PropTypes.array,
    schools: PropTypes.array,
    search: PropTypes.object,
    userId: PropTypes.number
};

App.defaultProps = {
    educators: [],
    schools: [],
    search: {},
    userId: 0
};

export const mapStateToProps = (state = {}) => ({
    educators: state.educators,
    industries: state.industries,
    schools: state.schools,
    search: state.search
});

export const mapDispatchToProps = dispatch => ({
    loadEducators: (url) => dispatch(loadEducators(url)),
    radiusChanged: (radius) => dispatch(radiusChanged(radius)),
    updatePrimaryIndustryQuery: (event) => dispatch(updatePrimaryIndustryQuery(event.target.value)),
    updateSecondaryIndustryQuery: (event) => dispatch(updateSecondaryIndustryQuery(event.target.value)),
    updateSchoolQuery: (event) => dispatch(updateSchoolQuery(event.target.value)),
    updateSearchQuery: (event) => dispatch(updateSearchQuery(event.target.value)),
    zipcodeChanged: (zipcode) => dispatch(zipcodeChanged(zipcode)),
});

const ConnectedApp = connect(
    mapStateToProps,
    mapDispatchToProps
)(App);

export default ConnectedApp;
