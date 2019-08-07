import React, { Component } from "react";
import PropTypes from "prop-types";
import * as api  from '../../utilities/api/api'

class FavoriteCompany extends Component {

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
            return <span className="favorite-company" data-uk-tooltip="title: Remove from My Favorites" onClick={this.unFavorite}>
                        <i className="fa fa-heart" aria-hidden="true"></i>
                    </span>
        } else {
            return <span className="favorite-company" data-uk-tooltip="title: Add to My Favorites" onClick={this.favorite}>
                        <i style={{ opacity: 0.5 }} className="fa fa-heart" aria-hidden="true"></i>
                    </span>
        }
    }

    unFavorite() {

        const url = window.Routing.generate("unfavorite_company", {id: this.props.id});

        return api.post(url)
            .then((response) => {
                if (response.statusCode < 300 && response.responseBody.success === true) {
                    this.setState({
                        isFavorited: !this.state.isFavorited
                    });
                    this.props.companyUnfavorited(this.props.id);
                }  else {
                    window.Pintex.notification("Unable to unfavorite company.  Please try again.");
                }
            })
            .catch(()=> {
                window.Pintex.notification("Unable to unfavorite company.  Please try again.");
            });
    }

    favorite() {
        const url = window.Routing.generate("favorite_company", {id: this.props.id});

        return api.post(url)
            .then((response) => {
                if (response.statusCode < 300 && response.responseBody.success === true) {
                    this.setState({
                        isFavorited: !this.state.isFavorited
                    });
                    this.props.companyFavorited(this.props.id);
                }  else {
                    window.Pintex.notification("Unable to favorite company.  Please try again.");
                }
            })
            .catch(()=> {
                window.Pintex.notification("Unable to favorite company.  Please try again.");
            });
    }
}

FavoriteCompany.propTypes = {
    id: PropTypes.number,
    isFavorited: PropTypes.bool,
    companyFavorited: PropTypes.func,
    companyUnfavorited: PropTypes.func,
    boundByProps: PropTypes.bool
};

FavoriteCompany.defaultProps = {
    boundByProps: false,
    companyFavorited: () => {},
    companyUnfavorited: () => {}
};

export default FavoriteCompany;
