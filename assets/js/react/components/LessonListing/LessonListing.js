import React, { Component } from "react";
import PropTypes from "prop-types";
import FavoriteLesson from "../FavoriteLesson/FavoriteLesson"
import TeachLesson from "../TeachLesson/TeachLesson"


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

                        <TeachLesson
                            boundByProps={true}
                            id={this.props.id}
                            isTeachable={this.props.isTeachable}
                            isTeacher={this.props.isTeacher}
                            lessonIsNowTeachable={this.props.lessonTeach}
                            lessonIsNowUnteachable={this.props.lessonUnteach}
                        />
                        &nbsp;&nbsp;&nbsp;
                        <FavoriteLesson
                            boundByProps={true}
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
        this.props.isTeachable ? this.props.lessonUnteach(lessonId) : this.props.lessonTeach(lessonId);
    }
}

LessonListing.propTypes = {
    description: PropTypes.string,
    id: PropTypes.number,
    isFavorite: PropTypes.bool,
    isTeachable: PropTypes.bool,
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
    isTeachable: false,
    isTeacher: false,
    lessonFavorited: () => {},
    lessonUnfavorited: () => {},
    lessonTeach: () => {},
    lessonUnteach: () => {}
};

export default LessonListing;
