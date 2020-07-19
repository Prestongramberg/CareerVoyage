import React, { Component } from "react";
import PropTypes from "prop-types";

class FavoriteVideo extends Component {

    constructor(props) {
        super(props);
        const methods = ["unFavorite", "favorite"];
        methods.forEach(method => (this[method] = this[method].bind(this)));

    }

    render() {
        if( this.props.isFavorite ) {
            return <span className="favorite-company" data-uk-tooltip="title: Remove from My Favorites" onClick={ this.unFavorite }>
                        <i className="fa fa-heart" aria-hidden="true"></i>
                    </span>
        } else {
            return <span className="favorite-company" data-uk-tooltip="title: Add to My Favorites" onClick={ this.favorite }>
                        <i style={{ opacity: 0.5 }} className="fa fa-heart" aria-hidden="true"></i>
                    </span>
        }
    }

    unFavorite() {

        const url = window.Routing.generate("unfavorite_video", {id: this.props.id});

        this.props.unfavoriteVideo(url, this.props.id);

    }

    favorite() {
        const url = window.Routing.generate("favorite_video", {id: this.props.id});

        this.props.favoriteVideo(url, this.props.id);

    }
}

FavoriteVideo.propTypes = {
    id: PropTypes.number,
    isFavorite: PropTypes.bool,
    favoriteVideo: PropTypes.func,
    unfavoriteVideo: PropTypes.func
};

FavoriteVideo.defaultProps = {
    favoriteVideo: () => {},
    unfavoriteVideo: () => {}
};

export default FavoriteVideo;
