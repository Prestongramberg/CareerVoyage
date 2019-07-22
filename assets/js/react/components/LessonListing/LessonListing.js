import React, { Component } from "react";
import PropTypes from "prop-types";

class LessonListing extends Component {

    render() {
        return (
            <div className="uk-card uk-card-default">
                <div className="uk-height-medium uk-flex uk-flex-right uk-flex-bottom uk-background-cover uk-light"
                     style={{backgroundImage: `url(${this.props.image})`}}>
                    <div className="uk-inline uk-padding-small">
                        <a href="#">
                            <i className="fa fa-heart" aria-hidden="true"></i>
                        </a>
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
}

LessonListing.propTypes = {
    description: PropTypes.string,
    id: PropTypes.number,
    image: PropTypes.string,
    title: PropTypes.string
};

LessonListing.defaultProps = {};

export default LessonListing;
