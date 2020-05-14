import React from "react"
import { connect } from "react-redux"
import { loadEducators, radiusChanged, updateSearchQuery, zipcodeChanged } from './actions/actionCreators'
import PropTypes from "prop-types";
import EducatorListing from "../../components/EducatorListing/EducatorListing";
import Loader from "../../components/Loader/Loader";

class App extends React.Component {

    constructor() {
        super();
        const methods = ["loadEducators", "getRelevantEducators"];
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
                                    <input className="uk-search-input" type="search" placeholder="Search by Name or Interests..." onChange={this.props.updateSearchQuery} value={this.props.search.query} />
                                </div>
                            </div>
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
            console.log(educator)
            // Set Searchable Fields
            const searchableFields = ["firstName", "lastName", "briefBio"];

            // Filter By Search Term
            if( this.props.search.query ) {
                // basic search fields
                const basicSearchFieldsFound = searchableFields.some((field) => ( educator[field] && educator[field].toLowerCase().indexOf(this.props.search.query.toLowerCase() ) > -1 ) )

                return basicSearchFieldsFound
            }

            return true;
        })

    }

    componentDidMount() {
        this.loadEducators();
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
    search: PropTypes.object,
    userId: PropTypes.number
};

App.defaultProps = {
    educators: [],
    search: {},
    userId: 0
};

export const mapStateToProps = (state = {}) => ({
    educators: state.educators,
    search: state.search
});

export const mapDispatchToProps = dispatch => ({
    loadEducators: (url) => dispatch(loadEducators(url)),
    radiusChanged: (radius) => dispatch(radiusChanged(radius)),
    updateSearchQuery: (event) => dispatch(updateSearchQuery(event.target.value)),
    zipcodeChanged: (zipcode) => dispatch(zipcodeChanged(zipcode)),
});

const ConnectedApp = connect(
    mapStateToProps,
    mapDispatchToProps
)(App);

export default ConnectedApp;
