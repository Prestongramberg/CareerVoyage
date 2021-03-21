import React, { Component } from "react";
import PropTypes from "prop-types";
import * as api  from '../../utilities/api/api'

class FavoriteLesson extends Component {

    constructor(props) {
        super(props);
        const methods = ["favorite", "renderBasedOnFavorite", "unFavorite"];
        methods.forEach(method => (this[method] = this[method].bind(this)));

        // Only used on Root Binding
        this.state = {
            isFavorited: props.isFavorited
        }
    }

    render() {
        return this.renderBasedOnFavorite( this.props.boundByProps ? this.props.isFavorited : this.state.isFavorited );
    }

    renderBasedOnFavorite( isFavorited ) {
        if( isFavorited ) {
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
                    window.Pintex.notification("Unable to unfavorite topic.  Please try again.");
                }
            })
            .catch(()=> {
                window.Pintex.notification("Unable to unfavorite topic.  Please try again.");
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
                    window.Pintex.notification("Unable to favorite topic.  Please try again.");
                }
            })
            .catch(()=> {
                window.Pintex.notification("Unable to favorite topic.  Please try again.");
            });
    }
}

FavoriteLesson.propTypes = {
    id: PropTypes.number,
    isFavorited: PropTypes.bool,
    lessonFavorited: PropTypes.func,
    lessonUnfavorited: PropTypes.func,
    boundByProps: PropTypes.bool
};

FavoriteLesson.defaultProps = {
    boundByProps: false,
    lessonFavorited: () => {},
    lessonUnfavorited: () => {}
};

export default FavoriteLesson;
