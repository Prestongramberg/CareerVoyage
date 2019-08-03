import React, { Component } from "react";
import PropTypes from "prop-types";
import FavoriteLesson from "../FavoriteLesson/FavoriteLesson"


class LessonListing extends Component {

    constructor() {
        super();
        const methods = ["toggleTeacher"];
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
                        <FavoriteLesson
                            id={this.props.id}
                            isFavorited={this.props.isFavorite}
                            lessonFavorited={this.props.lessonFavorited}
                            lessonUnfavorited={this.props.lessonUnfavorited}
                        />
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
    lessonFavorited: PropTypes.func,
    lessonUnfavorited: PropTypes.func,
    lessonTeach: PropTypes.func,
    lessonUnteach: PropTypes.func,
    title: PropTypes.string
};

LessonListing.defaultProps = {
    isFavorite: false,
    isTeacher: false,
    lessonFavorited: () => {},
    lessonUnfavorited: () => {},
    lessonTeach: () => {},
    lessonUnteach: () => {}
};

export default LessonListing;
