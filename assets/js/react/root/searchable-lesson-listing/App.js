import React from "react"
import { connect } from "react-redux"
import { loadLessons, loadIndustries, updateIndustryQuery, updateSearchQuery } from './actions/actionCreators'
import PropTypes from "prop-types";
import LessonListing from "../../components/LessonListing/LessonListing";

class App extends React.Component {

    constructor() {
        super();
        const methods = ["renderIndustryDropdown", "getRelevantLessons"];
        methods.forEach(method => (this[method] = this[method].bind(this)));
    }

    render() {

        const relevantLessons = this.getRelevantLessons();

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

                <div className="lesson-listings" uk-grid="masonry: true">
                    { this.props.search.loading && (
                        <div className="uk-width-1-1 uk-align-center">
                            <div data-uk-spinner></div>
                        </div>
                    )}
                    { !this.props.search.loading && relevantLessons.map(lesson => (
                        <div className="uk-width-1-1 uk-width-1-2@s uk-width-1-3@m" key={lesson.id}>
                            <LessonListing
                                description={'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud'}
                                id={lesson.id}
                                image={lesson.thumbnailImageURL}
                                isFavorite={lesson.favorite}
                                isTeachable={lesson.teachable}
                                title={lesson.title} />
                        </div>
                    ))}
                    { !this.props.search.loading && relevantLessons.length === 0 && (
                        <p>No results match your selection</p>
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
                        <option value="">Filter by Course...</option>
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

    getRelevantLessons () {

        return this.props.lessons.filter(lesson => {

            // Set Searchable Fields
            const searchableFields = ["title"];

            // Filter Category
            // if ( !!this.props.search.industry && parseInt(lesson.primaryIndustry.id ) !== parseInt( this.props.search.industry ) ) {
            //     return false;
            // }

            // Filter By Search Term
            if( this.props.search.query ) {
                return searchableFields.some((field) => lesson[field].toLowerCase().indexOf(this.props.search.query.toLowerCase() ) > -1 );
            }

            return true;
        })

    }

    componentDidMount() {
        this.props.loadLessons( window.Routing.generate('get_lessons') );
        this.props.loadIndustries( window.Routing.generate('get_industries') );
    }
}

App.propTypes = {
    search: PropTypes.object,
    industries: PropTypes.array,
    lessons: PropTypes.array
};

App.defaultProps = {
    industries: [],
    lessons: [],
    search: {}
};

export const mapStateToProps = (state = {}) => ({
    industries: state.industries,
    lessons: state.lessons,
    search: state.search
});

export const mapDispatchToProps = dispatch => ({
    loadLessons: (url) => dispatch(loadLessons(url)),
    loadIndustries: (url) => dispatch(loadIndustries(url)),
    updateIndustryQuery: (event) => dispatch(updateIndustryQuery(event.target.value)),
    updateSearchQuery: (event) => dispatch(updateSearchQuery(event.target.value)),
});

const ConnectedApp = connect(
    mapStateToProps,
    mapDispatchToProps
)(App);

export default ConnectedApp;