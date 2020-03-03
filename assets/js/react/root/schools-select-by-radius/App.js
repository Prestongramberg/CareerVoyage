import React from "react"
import { connect } from "react-redux"
import { loadSchools, radiusChanged, schoolToggled, selectAll, unSelectAll, zipcodeChanged } from './actions/actionCreators'
import PropTypes from "prop-types";
import Loader from "../../components/Loader/Loader";

class App extends React.Component {

    constructor() {
        super();
        const methods = ["loadSchools"];
        methods.forEach(method => (this[method] = this[method].bind(this)));
    }

    render() {

        const ranges = [ 25, 50, 70, 150 ];

        return (
            <div className="uk-container">

                <div className="uk-grid-small uk-flex-middle" data-uk-grid>
                    <div className="uk-width-1-1 uk-width-1-1@s uk-width-1-3@l">
                        <div className="uk-search uk-search-default uk-width-1-1">
                            <span data-uk-search-icon></span>
                            <input className="uk-search-input" type="search" placeholder="Enter Zip Code..." onChange={(e) => { this.props.zipcodeChanged( e.target.value ) }} value={ this.props.search.geoZipCodeValue } />
                        </div>
                    </div>
                    <div className="uk-width-1-1 uk-width-1-1@s uk-width-1-3@l">
                        <select className="uk-select" onChange={(e) => { this.props.radiusChanged( e.target.value ) }} value={ parseInt( this.props.search.geoRadiusValue ) }>
                            {ranges.map( (range, i) => <option key={i} value={range}>{range} miles</option> )}
                        </select>
                    </div>
                    <div className="uk-width-1-1 uk-width-1-1@s uk-width-1-3@l">
                        <div className="uk-button uk-button-primary" onClick={this.loadSchools}>Apply</div>
                    </div>
                </div>

                <div className="uk-margin">

                    {this.props.search.loading ? (
                        <Loader />
                    ) : (
                        <div>

                            {this.props.schools.length ? (
                                <div>
                                    <a className="uk-link-muted" onClick={this.props.selectAll}>Select All</a> - <a className="uk-link-muted" onClick={this.props.unSelectAll}>UnSelect All</a>
                                    <div id="new_company_form_schools" className="uk-checkboxes uk-checkboxes-newlines">
                                        {this.props.schools.map((school) => (
                                            <>
                                                <input
                                                    checked={ this.props.search.schoolStatus[`s${school.id}`] === true }
                                                    className="uk-checkbox"
                                                    type="checkbox"
                                                    id={ `${this.props.search.fieldName}_${school.id}` }
                                                    name={ this.props.search.fieldName }
                                                    value={ school.id }
                                                    onChange={(e) => {
                                                        this.props.schoolToggled(school.id);
                                                    }}
                                                />
                                                <label htmlFor={`${this.props.search.fieldName}_${school.id}`}>{ school.name }</label>
                                            </>
                                        ))}
                                        <input type="hidden" name={ this.props.search.geoRadiusName } value={ this.props.search.geoRadiusValue } />
                                        <input type="hidden" name={ this.props.search.geoZipCodeName } value={ this.props.search.geoZipCodeValue } />
                                    </div>
                                </div>

                            ) : (
                                <p>Your selection did not match any schools.  Please retry your selection.</p>
                            )}

                        </div>
                    )}
                </div>
            </div>
        )
    }

    componentDidMount() {
        this.loadSchools();
    }

    loadSchools() {
        this.props.loadSchools( window.Routing.generate('get_schools_by_radius') + `?zipcode=${this.props.search.geoZipCodeValue}&radius=${this.props.search.geoRadiusValue}` );
    }
}

App.propTypes = {
    search: PropTypes.object,
    schools: PropTypes.array
};

App.defaultProps = {
    search: {},
    schools: []
};

export const mapStateToProps = (state = {}) => ({
    search: state.search,
    schools: state.schools
});

export const mapDispatchToProps = dispatch => ({
    loadSchools: (url) => dispatch(loadSchools(url)),
    radiusChanged: (radius) => dispatch(radiusChanged(radius)),
    selectAll: () => dispatch(selectAll()),
    schoolToggled: (schoolId) => dispatch(schoolToggled(schoolId)),
    unSelectAll: () => dispatch(unSelectAll()),
    zipcodeChanged: (zipcode) => dispatch(zipcodeChanged(zipcode)),
});

const ConnectedApp = connect(
    mapStateToProps,
    mapDispatchToProps
)(App);

export default ConnectedApp;
