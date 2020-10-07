import React, { Component } from "react";
import PropTypes from "prop-types";
import FavoriteCompany from "../FavoriteCompany/FavoriteCompany";
import {truncate} from "../../utilities/string-utils";
import FavoriteVideo from "../FavoriteVideo/FavoriteVideo";
import * as api from "../../utilities/api/api";

class VideoListing extends Component {

    constructor(props) {

        super(props);
        const methods = [];
        methods.forEach(method => (this[method] = this[method].bind(this)));
    }

    render() {
        debugger;
        return (
            <div>

                <div className="uk-card uk-card-default">
                    {
                        <div className="uk-card-header">
                            <div className="uk-width-expand">
                                <FavoriteVideo
                                    id={this.props.id}
                                    isFavorite={this.props.isFavorite}
                                    favoriteVideo={this.props.favoriteVideo}
                                    unfavoriteVideo={this.props.unfavoriteVideo}
                                />

                                { this.props.careerVideoPage && this.props.user && this.props.user.roles && (Object.values(this.props.user.roles).indexOf("ROLE_ADMIN_USER") !== -1 || Object.values(this.props.user.roles).indexOf("ROLE_SITE_ADMIN_USER") !== -1) &&

                                    [
                                        <a style={{marginLeft: "20px"}} className="uk-link-text" href={Routing.generate('video_index') + "?editVideo=" + this.props.id }>Edit</a>,
                                        "|",
                                        <a className="uk-link-text" href={Routing.generate('career_videos_delete', {id: this.props.id}) }>Delete</a>

                                    ]
                                }

                            </div>
                        </div>
                    }

                    <div className="uk-card-body">
                        <div>
                            <a className="company-video uk-inline" target="_blank"
                               href={"https://www.youtube.com/watch?v=" + this.props.videoId.trim() }>
                                <img src={"http://i.ytimg.com/vi/" + this.props.videoId.trim() + "/hqdefault.jpg"} alt="" />
                                <div className="company-video__overlay">
                                    <div className="company-video__overlay-title">
                                        {this.props.name}
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        );
    }
}


VideoListing.propTypes = {
    id: PropTypes.number,
    isFavorite: PropTypes.bool,
    careerVideoPage: PropTypes.bool,
    videoId: PropTypes.string,
    tags: PropTypes.string,
    name: PropTypes.string,
    favoriteVideo: PropTypes.func,
    unfavoriteVideo: PropTypes.func,
    user: PropTypes.object,
    secondaryIndustries: PropTypes.array
};

VideoListing.defaultProps = {
    favoriteVideo: () => {},
    unfavoriteVideo: () => {},
    careerVideoPage: false,
    user: {},
    tags: '',
    secondaryIndustries: []
};

export default VideoListing;
