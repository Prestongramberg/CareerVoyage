import React from "react"
import { connect } from "react-redux"
import { loadProfessionals, updatePrimaryIndustryQuery, updateSearchQuery } from './actions/actionCreators'
import PropTypes from "prop-types";
import ProfessionalListing from "../../components/ProfessionalListing/ProfessionalListing";

class App extends React.Component {

    constructor() {
        super();
        const methods = ["renderIndustryDropdown", "getRelevantProfessionals"];
        methods.forEach(method => (this[method] = this[method].bind(this)));
    }

    render() {

        const relevantProfessionals = this.getRelevantProfessionals();

        return (
            <div className="uk-container">
                <ul className="" data-uk-tab="{connect: '#tab-professionals'}" data-uk-switcher>
                    <li className="uk-active"><a href="#all-companies">All Professionals</a></li>
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
                            { this.renderIndustryDropdown() }
                        </div>

                        <div className="uk-grid" data-uk-grid>
                            <div className="uk-width-1-1 professional-listings">
                                { this.props.search.loading && (
                                    <div className="uk-width-1-1 uk-align-center">
                                        <div data-uk-spinner></div>
                                    </div>
                                )}
                                { !this.props.search.loading && relevantProfessionals.map(professional => (
                                    <ProfessionalListing />
                                ))}
                                { !this.props.search.loading && relevantProfessionals.length === 0 && (
                                    <p>No professionals match your selection</p>
                                )}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        )
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

    getRelevantProfessionals () {

        return this.props.professionals.filter(professional => {

            // Set Searchable Fields
            const searchableFields = ["name", "shortDescription"];

            // Filter Category
            if ( !!this.props.search.industry && parseInt(professional.primaryIndustry.id ) !== parseInt( this.props.search.industry ) ) {
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
    professionals: [],
    search: {},
    userId: 0
};

export const mapStateToProps = (state = {}) => ({
    professionals: state.professionals,
    search: state.search
});

export const mapDispatchToProps = dispatch => ({
    loadProfessionals: (url) => dispatch(loadProfessionals(url)),
    updatePrimaryIndustryQuery: (event) => dispatch(updatePrimaryIndustryQuery(event.target.value)),
    updateSearchQuery: (event) => dispatch(updateSearchQuery(event.target.value)),
});

const ConnectedApp = connect(
    mapStateToProps,
    mapDispatchToProps
)(App);

export default ConnectedApp;