import React from "react"
import { connect } from "react-redux"
import { lessonFavorited, lessonUnfavorited, lessonTeach, lessonUnteach, loadLessons, loadUser, updateCourseQuery, updateSearchQuery } from './actions/actionCreators'
import PropTypes from "prop-types";
import LessonListing from "../../components/LessonListing/LessonListing";

class App extends React.Component {

    constructor() {
        super();
        const methods = ["renderCourseDropdown", "getRelevantLessons"];
        methods.forEach(method => (this[method] = this[method].bind(this)));
    }

    render() {

        const relevantLessons = this.getRelevantLessons();
        const favoriteLessons = this.props.lessons.filter(lesson => lesson.favorite === true);
        const teachableLessons = this.props.lessons.filter(lesson => lesson.teachable === true);
        const ownerLessons = this.props.user.lessons || [];

        return (
            <div className="uk-container">
                <ul className="" data-uk-tab="{connect: '#tab-lessons'}" data-uk-switcher>
                    <li className="uk-active"><a href="#all-lessons">All Lessons</a></li>
                    <li><a href="#favorite-lessons">Favorites</a></li>
                    <li><a href="#teachable-lessons">Teachable Lessons</a></li>
                    <li><a href="#my-lessons">My Lessons</a></li>

                </ul>

                <div className="uk-switcher" id="tab-lessons">
                    <div className="lessons__all">
                        <div>
                            <div className="uk-grid-small uk-flex-middle" data-uk-grid>
                                <div className="uk-width-1-1 uk-width-1-1@s uk-width-1-3@l">
                                    <form className="uk-search uk-search-default uk-width-1-1">
                                        <span data-uk-search-icon></span>
                                        <input className="uk-search-input" type="search" placeholder="Search by Name..." onChange={this.props.updateSearchQuery} value={this.props.search.query} />
                                    </form>
                                </div>
                                { this.renderCourseDropdown() }
                            </div>

                            <div className="lesson-listings" data-uk-grid="masonry: true">
                                { this.props.search.loading && (
                                    <div className="uk-width-1-1 uk-align-center">
                                        <div data-uk-spinner></div>
                                    </div>
                                )}
                                { !this.props.search.loading && relevantLessons.map(lesson => (
                                    <div className="uk-width-1-1 uk-width-1-2@s uk-width-1-3@m" key={lesson.id}>
                                        <LessonListing
                                            description={lesson.shortDescription}
                                            id={lesson.id}
                                            image={lesson.thumbnailImageURL}
                                            isFavorite={lesson.favorite}
                                            isTeacher={lesson.teachable}
                                            lessonFavorited={this.props.lessonFavorited}
                                            lessonUnfavorited={this.props.lessonUnfavorited}
                                            lessonTeach={this.props.lessonTeach}
                                            lessonUnteach={this.props.lessonUnteach}
                                            title={lesson.title} />
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
                    <div className="lessons_library">
                        { favoriteLessons.length > 0 && (
                            <div className="lesson-listings" data-uk-grid="masonry: true">
                                { favoriteLessons.map(lesson => (
                                    <div className="uk-width-1-1 uk-width-1-2@s uk-width-1-3@m" key={lesson.id}>
                                        <LessonListing
                                            description={lesson.shortDescription}
                                            id={lesson.id}
                                            image={lesson.thumbnailImageURL}
                                            isFavorite={lesson.favorite}
                                            isTeacher={lesson.teachable}
                                            lessonFavorited={this.props.lessonFavorited}
                                            lessonUnfavorited={this.props.lessonUnfavorited}
                                            lessonTeach={this.props.lessonTeach}
                                            lessonUnteach={this.props.lessonUnteach}
                                            title={lesson.title} />
                                    </div>
                                ))}
                            </div>
                        )}
                        { favoriteLessons.length === 0 && (
                            <div className="uk-placeholder uk-text-center uk-width-1-1">
                                <p>You don't have any favorite <i className="fa fa-heart" aria-hidden="true"></i> lessons yet.</p>
                            </div>
                        )}
                    </div>
                    <div className="lessons_teachable">
                        { teachableLessons.length > 0 && (
                            <div className="lesson-listings uk-margin" data-uk-grid="masonry: true">
                                { teachableLessons.map(lesson => (
                                    <div className="uk-width-1-1 uk-width-1-2@s uk-width-1-3@m" key={lesson.id}>
                                        <LessonListing
                                            description={lesson.shortDescription}
                                            id={lesson.id}
                                            image={lesson.thumbnailImageURL}
                                            isFavorite={lesson.favorite}
                                            isTeacher={lesson.teachable}
                                            lessonFavorited={this.props.lessonFavorited}
                                            lessonUnfavorited={this.props.lessonUnfavorited}
                                            lessonTeach={this.props.lessonTeach}
                                            lessonUnteach={this.props.lessonUnteach}
                                            title={lesson.title} />
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
                        <div className="uk-margin">
                            <div className="uk-flex uk-flex-right">
                                <a href={ window.Routing.generate('lesson_new') } className="uk-button uk-button-primary uk-button-small">Create a Lesson</a>
                            </div>
                        </div>
                        { ownerLessons.length > 0 && (
                            <div className="lesson-listings uk-margin" data-uk-grid="masonry: true">
                                { ownerLessons.map(lesson => (
                                    <div className="uk-width-1-1 uk-width-1-2@s uk-width-1-3@m" key={lesson.id}>
                                        <LessonListing
                                            description={lesson.shortDescription}
                                            id={lesson.id}
                                            image={lesson.thumbnailImageURL}
                                            isFavorite={lesson.favorite}
                                            isTeacher={lesson.teachable}
                                            lessonFavorited={this.props.lessonFavorited}
                                            lessonUnfavorited={this.props.lessonUnfavorited}
                                            lessonTeach={this.props.lessonTeach}
                                            lessonUnteach={this.props.lessonUnteach}
                                            title={lesson.title} />
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
                return searchableFields.some((field) => lesson[field] && lesson[field].toLowerCase().indexOf(this.props.search.query.toLowerCase() ) > -1 );
            }

            return true;
        })

    }

    componentDidMount() {
        this.props.loadLessons( window.Routing.generate('get_lessons') );
        this.props.loadUser( window.Routing.generate('logged_in_user') );
    }
}

App.propTypes = {
    courses: PropTypes.array,
    lessons: PropTypes.array,
    search: PropTypes.object,
    user: PropTypes.object
};

App.defaultProps = {
    courses: [],
    lessons: [],
    search: {},
    user: {}
};

export const mapStateToProps = (state = {}) => ({
    courses: state.courses,
    lessons: state.lessons,
    search: state.search,
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