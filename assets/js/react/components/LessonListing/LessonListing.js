import React, { Component } from "react";
import PropTypes from "prop-types";

class LessonListing extends Component {

    constructor() {
        super();
        const methods = ["toggleFavorite", "toggleTeacher"];
        methods.forEach(method => (this[method] = this[method].bind(this)));
    }

    render() {
        return (
            <div className="lesson-listing uk-card uk-card-default">
                <div className="lesson-listing__image uk-height-medium uk-flex uk-flex-right uk-flex-bottom uk-background-cover uk-light"
                     style={{backgroundImage: `url(${this.props.image})`}}>
                    <div className="uk-inline uk-padding-small">
                        { this.props.isTeacher && <span className="teach-lesson" data-uk-tooltip="title: Remove from My Teachable Lessons" onClick={this.toggleTeacher}>
                            <i className="fa fa-graduation-cap" aria-hidden="true"></i>
                        </span> }
                        { !this.props.isTeacher && <span className="teach-lesson" data-uk-tooltip="title: Add to My Teachable Lessons" onClick={this.toggleTeacher}>
                            <i style={{ opacity: 0.5 }} className="fa fa-graduation-cap" aria-hidden="true"></i>
                        </span> }
                        &nbsp;&nbsp;&nbsp;
                        { this.props.isFavorite && <span className="favorite-lesson" data-uk-tooltip="title: Remove from My Favorites" onClick={this.toggleFavorite}>
                            <i className="fa fa-heart" aria-hidden="true"></i>
                        </span> }
                        { !this.props.isFavorite && <span className="favorite-lesson" data-uk-tooltip="title: Add to My Favorites" onClick={this.toggleFavorite}>
                            <i style={{ opacity: 0.5 }} className="fa fa-heart" aria-hidden="true"></i>
                        </span> }
                    </div>
                </div>
                <div className="uk-card-body">
                    <a href={ window.Routing.generate('lesson_view', { id: this.props.id }) }>
                        <h3 className="uk-card-title-small">{ this.props.title }</h3>
                    </a>
                    <p>{ this.props.description }</p>
                </div>
            </div>
        );
    }

    toggleFavorite() {
        const lessonId = this.props.id;
        this.props.isFavorite ? this.props.lessonUnfavorite(lessonId) : this.props.lessonFavorite(lessonId);
    }

    toggleTeacher() {
        const lessonId = this.props.id;
        this.props.isTeacher ? this.props.lessonUnteach(lessonId) : this.props.lessonTeach(lessonId);
    }
}

LessonListing.propTypes = {
    description: PropTypes.string,
    id: PropTypes.number,
    isFavorite: PropTypes.bool,
    isTeacher: PropTypes.bool,
    image: PropTypes.string,
    lessonFavorite: PropTypes.func,
    lessonUnfavorite: PropTypes.func,
    lessonTeach: PropTypes.func,
    lessonUnteach: PropTypes.func,
    title: PropTypes.string
};

LessonListing.defaultProps = {
    isFavorite: false,
    isTeacher: false,
    lessonFavorite: () => {},
    lessonUnfavorite: () => {},
    lessonTeach: () => {},
    lessonUnteach: () => {}
};

export default LessonListing;
