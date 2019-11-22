import React from "react"
import { connect } from "react-redux"
import { lessonFavorited, lessonUnfavorited, lessonTeach, lessonUnteach, loadLessons, loadUser, updateCourseQuery, updateSearchQuery } from './actions/actionCreators'
import PropTypes from "prop-types";
import LessonListing from "../../components/LessonListing/LessonListing";
import Loader from "../../components/Loader/Loader";

class App extends React.Component {

    constructor() {
        super();
        const methods = ["renderLesson", "renderCourseDropdown", "getRelevantLessons"];
        methods.forEach(method => (this[method] = this[method].bind(this)));
    }

    render() {

        const { favorites, teachables, user } = this.props;

        const relevantLessons = this.getRelevantLessons();
        const favoriteLessons = this.props.lessons.filter(lesson => favorites.indexOf(lesson.id) > -1 );
        const teachableLessons = this.props.lessons.filter(lesson => teachables.indexOf(lesson.id) > -1 );
        const ownerLessons = user.lessons || [];

        return (
            <div className="uk-container">
                <ul className="" data-uk-tab="{connect: '#tab-lessons'}" data-uk-switcher>
                    <li className="uk-active"><a href="#all-lessons">All Lessons</a></li>
                    <li><a href="#teachable-lessons">
                        {user.educator ? 'Lessons I want taught' : 'Lessons I can teach'}
                    </a></li>
                    <li><a href="#my-lessons">My Created Lessons</a></li>

                </ul>

                <div className="uk-switcher" id="tab-lessons">
                    <div className="lessons__all">
                        <div>
                            <div className="uk-grid-small uk-flex-middle" data-uk-grid>
                                <div className="uk-width-1-1 uk-width-1-1@s uk-width-1-3@l">
                                    <div className="uk-search uk-search-default uk-width-1-1">
                                        <span data-uk-search-icon></span>
                                        <input className="uk-search-input" type="search" placeholder="Search by Name..." onChange={this.props.updateSearchQuery} value={this.props.search.query} />
                                    </div>
                                </div>
                                { this.renderCourseDropdown() }
                            </div>

                            <div className="lesson-listings" data-uk-grid="masonry: true">
                                { this.props.search.loading && (
                                    <div className="uk-width-1-1 uk-align-center">
                                        <Loader />
                                    </div>
                                )}
                                { !this.props.search.loading && relevantLessons.map(lesson => (
                                    <div className="uk-width-1-1 uk-width-1-2@s uk-width-1-3@m" key={lesson.id}>
                                        { this.renderLesson( lesson ) }
                                    </div>
                                ))}
                                { !this.props.search.loading && relevantLessons.length === 0 && (
                                    <div className="uk-width-1-1">
                                        <div className="uk-placeholder uk-text-center">
                                            No lessons match your search criteria
                                        </div>
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>
                    <div className="lessons_teachable">
                        { teachableLessons.length > 0 && (
                            <div className="lesson-listings uk-margin" data-uk-grid="masonry: true">
                                { teachableLessons.map(lesson => (
                                    <div className="uk-width-1-1 uk-width-1-2@s uk-width-1-3@m" key={lesson.id}>
                                        { this.renderLesson( lesson ) }
                                    </div>
                                ))}
                            </div>
                        )}
                        { teachableLessons.length === 0 && (
                            <div className="uk-placeholder uk-text-center uk-width-1-1 uk-margin">
                                <p>You don't have any teachable <i className="fa fa-graduation-cap" aria-hidden="true"></i> lessons yet!</p>
                            </div>
                        )}
                    </div>
                    <div className="lessons_mine">
                        { ownerLessons.length > 0 && (
                            <div className="lesson-listings uk-margin" data-uk-grid="masonry: true">
                                { ownerLessons.map(lesson => (
                                    <div className="uk-width-1-1 uk-width-1-2@s uk-width-1-3@m" key={lesson.id}>
                                        { this.renderLesson( lesson ) }
                                    </div>
                                ))}
                            </div>
                        )}
                        { ownerLessons.length === 0 && (
                            <div className="uk-placeholder uk-text-center uk-width-1-1 uk-margin">
                                <p>You haven't created any lessons yet!</p>
                            </div>
                        )}
                    </div>
                </div>
            </div>
        )
    }

    renderCourseDropdown() {

        if ( this.props.courses.length > 0 ) {
            return <div className="uk-width-1-1 uk-width-1-2@s uk-width-1-3@l">
                <div className="uk-width-1-1 uk-text-truncate" data-uk-form-custom="target: > * > span:first-child">
                    <select onChange={this.props.updateCourseQuery}>
                        <option value="">Filter by Course...</option>
                        { this.props.courses.map( course => <option key={course.id} value={course.id}>{course.title}</option> ) }
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
            const lessonCourseIds = lesson.secondaryCourses.map(course => course.id);
            const lessonSecondaryCourseNames = lesson.secondaryCourses.map( course => course.title.toLowerCase() );

            // Add a Primary Course If Applicable
            if( lesson.primaryCourse && lesson.primaryCourse.id ) {
                lessonCourseIds.concat([ lesson.primaryCourse.id ]);
            }

            // Filter Category
            if ( !!this.props.search.course && lessonCourseIds.indexOf( parseInt( this.props.search.course ) ) === -1 ) {
                return false;
            }

            // Filter By Search Term
            if( this.props.search.query ) {
                // basic search fields
                const basicSearchFieldsFound = searchableFields.some((field) => ( lesson[field] && lesson[field].toLowerCase().indexOf(this.props.search.query.toLowerCase() ) > -1 ) )

                // primary course field
                const primaryCourseFound = lesson['primaryCourse'] && lesson['primaryCourse']['title'].toLowerCase().indexOf(this.props.search.query.toLowerCase() ) > -1

                // secondary course field
                const secondaryCourseFound = lessonSecondaryCourseNames.some((courseName) => ( courseName.toLowerCase().indexOf(this.props.search.query.toLowerCase() ) > -1 ) )

                return basicSearchFieldsFound || primaryCourseFound || secondaryCourseFound
            }

            return true;
        })

    }

    renderLesson( lesson ) {

        const isFavorited = this.props.favorites.indexOf(lesson.id) > -1;
        const isTeachable = this.props.teachables.indexOf(lesson.id) > -1;

        return <LessonListing
            description={lesson.shortDescription}
            id={lesson.id}
            image={lesson.thumbnailImageURL}
            isFavorite={isFavorited}
            isTeacher={isTeachable}
            lessonFavorited={this.props.lessonFavorited}
            lessonUnfavorited={this.props.lessonUnfavorited}
            lessonTeach={this.props.lessonTeach}
            lessonUnteach={this.props.lessonUnteach}
            title={lesson.title} />
    }

    componentDidMount() {
        this.props.loadLessons( window.Routing.generate('get_lessons') );
        this.props.loadUser( window.Routing.generate('logged_in_user') );
    }
}

App.propTypes = {
    courses: PropTypes.array,
    favorites: PropTypes.array,
    lessons: PropTypes.array,
    search: PropTypes.object,
    teachables: PropTypes.array,
    user: PropTypes.object
};

App.defaultProps = {
    courses: [],
    favorites: [],
    lessons: [],
    search: {},
    teachables: [],
    user: {}
};

export const mapStateToProps = (state = {}) => ({
    courses: state.courses,
    favorites: state.favorites,
    lessons: state.lessons,
    search: state.search,
    teachables: state.teachables,
    user: state.user
});

export const mapDispatchToProps = dispatch => ({
    lessonFavorited: (lessonId) => dispatch(lessonFavorited(lessonId)),
    lessonUnfavorited: (lessonId) => dispatch(lessonUnfavorited(lessonId)),
    lessonTeach: (lessonId) => dispatch(lessonTeach(lessonId)),
    lessonUnteach: (lessonId) => dispatch(lessonUnteach(lessonId)),
    loadLessons: (url) => dispatch(loadLessons(url)),
    loadUser: (url) => dispatch(loadUser(url)),
    updateCourseQuery: (event) => dispatch(updateCourseQuery(event.target.value)),
    updateSearchQuery: (event) => dispatch(updateSearchQuery(event.target.value)),
});

const ConnectedApp = connect(
    mapStateToProps,
    mapDispatchToProps
)(App);

export default ConnectedApp;
