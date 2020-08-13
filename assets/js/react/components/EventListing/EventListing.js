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
                                <a href={ window.Routing.generate('experience_view', { id: this.props.id }) }>
                                    <h4 className="uk-card-title-small">{ this.props.title }
                                    </h4>
                                    <small>{ this.props.friendlyName }</small>
                                    <br />
                                    <small>{ this.props.experienceListTitle }</small>
                                </a>
                            </div>

                        </div>
                    </div>
                    <div className="uk-card-body">

                        <div className="uk-margin">
                            { truncate( this.props.briefDescription ) || "This experience doesn't have a description yet." }
                        </div>
                        <div className="uk-margin">Start Date: { this.props.friendlyStartDateAndTime }</div>
                        <div className="uk-margin">End Date: { this.props.friendlyEndDateAndTime }</div>

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
