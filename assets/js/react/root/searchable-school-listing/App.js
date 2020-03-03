import React from "react"
import { connect } from "react-redux"
import { loadSchools, loadUser, radiusChanged, updateSearchQuery, zipcodeChanged } from './actions/actionCreators'
import PropTypes from "prop-types";
import Loader from "../../components/Loader/Loader";
import SchoolListing from "../../components/SchoolListing/SchoolListing"

class App extends React.Component {

    constructor() {
        super();
        const methods = ["getRelevantSchools", "loadSchools"];
        methods.forEach(method => (this[method] = this[method].bind(this)));
    }

    render() {

        const { user } = this.props;
        const relevantSchools = this.getRelevantSchools();
        const ranges = [ 25, 50, 70, 150 ];

        return (
            <div className="uk-container">
                <ul className="" data-uk-tab="{connect: '#tab-companies'}" data-uk-switcher>
                    <li className="uk-active"><a href="#all-companies">All Schools</a></li>
                </ul>

                <div className="uk-switcher" id="tab-companies">
                    <div className="schools__all">
                        <div className="uk-grid-small uk-flex-middle" data-uk-grid>
                            <div className="uk-width-1-1 uk-width-1-1@s uk-width-1-3@l">
                                <div className="uk-search uk-search-default uk-width-1-1">
                                    <span data-uk-search-icon></span>
                                    <input className="uk-search-input" type="search" placeholder="Search..." onChange={this.props.updateSearchQuery} value={this.props.search.query} />
                                </div>
                            </div>
                            <div className="uk-width-1-1 uk-width-1-1@s uk-width-1-3@l">
                                <div className="uk-search uk-search-default uk-width-1-1">
                                    <span data-uk-location-icon></span>
                                    <input className="uk-search-input" type="search" placeholder="Enter Zip Code..." onChange={(e) => { this.props.zipcodeChanged( e.target.value ) }} value={ this.props.search.zipcode } />
                                </div>
                            </div>
                            <div className="uk-width-1-1 uk-width-1-1@s uk-width-1-3@l">
                                <div className="uk-grid-small uk-flex-middle" data-uk-grid>
                                    <div className="uk-width-expand">
                                        <select className="uk-select" onChange={(e) => { this.props.radiusChanged( e.target.value ) }} value={ parseInt( this.props.search.radius ) }>
                                            {ranges.map( (range, i) => <option key={i} value={range}>{range} miles</option> )}
                                        </select>
                                    </div>
                                    <div className="uk-width-auto">
                                        <div className="uk-button uk-button-primary" onClick={this.loadSchools}>Apply</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div className="uk-grid" data-uk-grid>
                            <div className="uk-width-1-1 company-listings">
                                { this.props.search.loading && (
                                    <div className="uk-width-1-1 uk-align-center">
                                        <Loader />
                                    </div>
                                )}
                                { !this.props.search.loading && relevantSchools.map(school => (
                                    <SchoolListing
                                        description={school.shortDescription}
                                        directions={(
                                            school.latitude && school.longitude ? `http://maps.google.com/?q=${ school.street },${ school.city },${ school.zipcode }` : ''
                                        )}
                                        email={school.email}
                                        id={school.id}
                                        image={school.thumbnailImageURL}
                                        key={school.id}
                                        linkedIn={school.schoolLinkedInPage}
                                        name={school.name}
                                        phone={school.phone}
                                        website={school.website} />
                                ))}
                                { !this.props.search.loading && relevantSchools.length === 0 && (
                                    <p>No schools match your selection</p>
                                )}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        )
    }

    getRelevantSchools () {

        return this.props.schools.filter(school => {

            // Set Searchable Fields
            const searchableFields = ["name", "street", "city", "zipcode"];

            // Filter By Search Term
            if( this.props.search.query ) {
                return searchableFields.some((field) => school[field] && school[field].toLowerCase().indexOf(this.props.search.query.toLowerCase() ) > -1 );
            }

            return true;
        })

    }

    componentDidMount() {
        this.loadSchools();
        this.props.loadUser( window.Routing.generate('logged_in_user') );
    }

    loadSchools() {
        this.props.loadSchools( window.Routing.generate('get_schools_by_radius') + `?zipcode=${this.props.search.zipcode}&radius=${this.props.search.radius}` );
    }

}

App.propTypes = {
    schools: PropTypes.array,
    search: PropTypes.object,
    user: PropTypes.object
};

App.defaultProps = {
    schools: [],
    search: {},
    user: {}
};

export const mapStateToProps = (state = {}) => ({
    schools: state.schools,
    search: state.search,
    user: state.user
});

export const mapDispatchToProps = dispatch => ({
    loadSchools: (url) => dispatch(loadSchools(url)),
    loadUser: (url) => dispatch(loadUser(url)),
    radiusChanged: (radius) => dispatch(radiusChanged(radius)),
    updateSearchQuery: (event) => dispatch(updateSearchQuery(event.target.value)),
    zipcodeChanged: (zipcode) => dispatch(zipcodeChanged(zipcode)),
});

const ConnectedApp = connect(
    mapStateToProps,
    mapDispatchToProps
)(App);

export default ConnectedApp;
