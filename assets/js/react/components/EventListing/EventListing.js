import React, { Component } from "react";
import PropTypes from "prop-types";
import { truncate } from "../../utilities/string-utils";

class EventListing extends Component {

    render() {
        return (
            <div>
                <div className="uk-card uk-card-default">
                    <div className="uk-card-header">
                        <div className="uk-grid-small uk-flex-middle" data-uk-grid>

                            <div className="uk-width-auto">
                                <h4 className="uk-card-title-small">
                                    <a href={ window.Routing.generate('experience_view', { id: this.props.id }) }>{ this.props.title }</a>
                                </h4>
                                <p className="uk-text-small"><strong>{ this.props.friendlyName }</strong><br />{ this.props.experienceListTitle }</p>
                            </div>

                        </div>
                    </div>
                    <div className="uk-card-body">
                        <p>{ truncate( this.props.briefDescription ) || "This experience doesn't have a description yet." }</p>
                        <p className="uk-text-small"><strong>Dates:</strong><br />{ this.props.friendlyStartDateAndTime }<br />{ this.props.friendlyEndDateAndTime }</p>

                        <div className="uk-width-expand uk-visible@m">
                            <div className="uk-align-right">
                                <a href={ window.Routing.generate('experience_view', { id: this.props.id }) }
                                   className="uk-button uk-button-small uk-button-text uk-text-muted">More info</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        );
    }
}

EventListing.propTypes = {
    id: PropTypes.number,
    title: PropTypes.string,
    briefDescription: PropTypes.string,
    className: PropTypes.string,
    friendlyStartDateAndTime: PropTypes.string,
    friendlyEndDateAndTime: PropTypes.string,
    friendlyName: PropTypes.string,
    experienceListTitle: PropTypes.string
};

EventListing.defaultProps = {
};

export default EventListing;
