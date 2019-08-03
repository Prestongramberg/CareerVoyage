import React, { Component } from "react";
import PropTypes from "prop-types";
import * as api  from '../../utilities/api/api'

class FavoriteLesson extends Component {

    constructor(props) {
        super(props);
        const methods = ["favorite", "showError", "unFavorite"];
        methods.forEach(method => (this[method] = this[method].bind(this)));
        this.state = {
            isFavorited: props.isFavorited
        };
    }

    render() {

        if( this.state.isFavorited ) {
            return <span className="favorite-lesson" data-uk-tooltip="title: Remove from My Favorites" onClick={this.unFavorite}>
                        <i className="fa fa-heart" aria-hidden="true"></i>
                    </span>
        } else {
            return <span className="favorite-lesson" data-uk-tooltip="title: Add to My Favorites" onClick={this.favorite}>
                        <i style={{ opacity: 0.5 }} className="fa fa-heart" aria-hidden="true"></i>
                    </span>
        }
    }

    unFavorite() {

        const url = window.Routing.generate("unfavorite_lesson", {id: this.props.id});

        return api.post(url)
            .then((response) => {
                if (response.statusCode < 300 && response.responseBody.success === true) {
                    this.setState({
                        isFavorited: !this.state.isFavorited
                    });
                    this.props.lessonUnfavorited(this.props.id);
                }  else {
                    this.showError("Unable to unfavorite lesson.  Please try again.");
                }
            })
            .catch(()=> {
                this.showError("Unable to unfavorite lesson.  Please try again.");
            });
    }

    favorite() {
        const url = window.Routing.generate("favorite_lesson", {id: this.props.id});

        return api.post(url)
            .then((response) => {
                if (response.statusCode < 300 && response.responseBody.success === true) {
                    this.setState({
                        isFavorited: !this.state.isFavorited
                    });
                    this.props.lessonFavorited(this.props.id);
                }  else {
                    this.showError("Unable to favorite lesson.  Please try again.");
                }
            })
            .catch(()=> {
                this.showError("Unable to favorite lesson.  Please try again.");
            });
    }

    showError(text) {
        alert(text);
    }
}

FavoriteLesson.propTypes = {
    id: PropTypes.number,
    isFavorited: PropTypes.bool,
    lessonFavorited: PropTypes.func,
    lessonUnfavorited: PropTypes.func
};

FavoriteLesson.defaultProps = {
    lessonFavorited: () => {},
    lessonUnfavorited: () => {}
};

export default FavoriteLesson;
