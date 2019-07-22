import React, { Component } from "react";
import PropTypes from "prop-types";

class LessonListing extends Component {

    render() {
        return (
            <div className="uk-card uk-card-default">
                <div className="uk-height-medium uk-flex uk-flex-right uk-flex-bottom uk-background-cover uk-light"
                     style={{backgroundImage: `url(${this.props.image})`}}>
                    <div className="uk-inline uk-padding-small">
                        <span className="teach-lesson" onClick={this.toggleTeacher}>
                            { this.props.isTeacher && <i className="fa fa-graduation-cap" aria-hidden="true"></i> }
                            { !this.props.isTeacher && <i style={{ opacity: 0.5 }} className="fa fa-graduation-cap" aria-hidden="true"></i> }
                        </span>
                        &nbsp;&nbsp;&nbsp;
                        <span className="favorite-lesson" onClick={this.toggleFavorite}>
                            { this.props.isFavorite && <i className="fa fa-heart" aria-hidden="true"></i> }
                            { !this.props.isFavorite && <i style={{ opacity: 0.5 }} className="fa fa-heart" aria-hidden="true"></i> }
                        </span>
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

    }

    toggleTeacher() {

    }
}

LessonListing.propTypes = {
    description: PropTypes.string,
    id: PropTypes.number,
    isFavorite: PropTypes.bool,
    isTeacher: PropTypes.bool,
    image: PropTypes.string,
    title: PropTypes.string
};

LessonListing.defaultProps = {
    isFavorite: false,
    isTeacher: false
};

export default LessonListing;
