import React from "react"
import { connect } from "react-redux"
import { loadUser, loadVideos } from './actions/actionCreators'
import PropTypes from "prop-types";
import Loader from "../../components/Loader/Loader";
import VideoListing from "../../components/VideoListing/VideoListing";
import {
    updateCompanyQuery, updateCompanyVideoIndustryQuery, updateCompanyVideoSearchQuery, updateCareerVideoIndustryQuery, updateCareerVideoSearchQuery, favoriteVideo, unfavoriteVideo
} from "../searchable-video-listing/actions/actionCreators";
import CompanyListing from "../searchable-company-listing/App";

class App extends React.Component {

    constructor() {
        super();
        const methods = ["loadVideos", "renderCompanyVideoIndustryDropdown", "getRelevantCompanyVideos", "getRelevantCareerVideos", "renderCompanyDropdown", "renderCareerVideoIndustryDropdown", "getFavoritedVideos"];
        methods.forEach(method => (this[method] = this[method].bind(this)));
    }

    render() {

        const relevantCompanyVideos = this.getRelevantCompanyVideos();
        const relevantCareerVideos = this.getRelevantCareerVideos();
        const favoritedVideos = this.getFavoritedVideos();

        return (
            <div className="uk-container">


                <ul className="" data-uk-tab="{connect: '#tab-companies'}" data-uk-switcher>
                    <li className="uk-active"><a href="#local-company-videos">Local Company Videos</a></li>
                    <li className="uk-active"><a href="#general-career-videos">General Career Videos</a></li>
                    <li><a href="#favorite-videos">Favorites</a></li>
                </ul>

                <div className="uk-switcher" id="tab-company-videos">
                    <div className="local_company_videos">

                        <div className="uk-grid-small uk-flex-middle" data-uk-grid>
                            <div className="uk-width-1-1 uk-width-1-1@s uk-width-1-3@l">
                                <div className="uk-search uk-search-default uk-width-1-1">
                                    <span data-uk-search-icon></span>
                                    <input className="uk-search-input" type="search" placeholder="Search by keyword or profession..." onChange={this.props.updateCompanyVideoSearchQuery} value={this.props.companyVideoSearch.query} />
                                </div>
                            </div>
                            { this.renderCompanyVideoIndustryDropdown() }
                            { this.renderCompanyDropdown() }
                        </div>

                        <div className="videos-listings" data-uk-grid="masonry: true">
                            { this.props.search.loading && (
                                <div className="uk-width-1-1 uk-align-center">
                                    <Loader />
                                </div>
                            )}
                            { !this.props.search.loading && relevantCompanyVideos.map(video => (
                                <VideoListing
                                    id={video.id}
                                    videoId={video.videoId}
                                    isFavorite={video.favorite}
                                    name={video.name}
                                    favoriteVideo={this.props.favoriteVideo}
                                    unfavoriteVideo={this.props.unfavoriteVideo}
                                />
                            ))}
                            { !this.props.search.loading && relevantCompanyVideos.length === 0 && (
                                <p>No videos match your selection</p>
                            )}
                        </div>
                    </div>

                    <div className="general_career_videos">

                        <div className="uk-grid-small uk-flex-middle" data-uk-grid>
                            <div className="uk-width-1-1 uk-width-1-1@s uk-width-1-3@l">
                                <div className="uk-search uk-search-default uk-width-1-1">
                                    <span data-uk-search-icon></span>
                                    <input className="uk-search-input" type="search" placeholder="Search by keyword or profession..." onChange={this.props.updateCareerVideoSearchQuery} value={this.props.careerVideoSearch.query} />
                                </div>
                            </div>

                            { this.renderCareerVideoIndustryDropdown() }
                        </div>

                        <div className="videos-listings" data-uk-grid="masonry: true">
                            { this.props.search.loading && (
                                <div className="uk-width-1-1 uk-align-center">
                                    <Loader />
                                </div>
                            )}
                            { !this.props.search.loading && relevantCareerVideos.map(video => (
                                [
                                    <VideoListing
                                        id={video.id}
                                        videoId={video.videoId}
                                        isFavorite={video.favorite}
                                        name={video.name}
                                        favoriteVideo={this.props.favoriteVideo}
                                        unfavoriteVideo={this.props.unfavoriteVideo}
                                        careerVideoPage={true}
                                        user={this.props.user}
                                        tags={video.tags}
                                        secondaryIndustries ={video.secondaryIndustries}
                                    />
                                ]

                            ))}

                            { !this.props.search.loading && relevantCareerVideos.length === 0 && (
                                <p>No videos match your selection</p>
                            )}
                        </div>
                    </div>

                    <div className="favorite_videos" data-uk-grid="masonry: true">
                        { this.props.search.loading && (
                            <div className="uk-width-1-1 uk-align-center">
                                <Loader />
                            </div>
                        )}
                        { !this.props.search.loading && favoritedVideos.map(video => (
                            <VideoListing
                                id={video.id}
                                videoId={video.videoId}
                                isFavorite={video.favorite}
                                name={video.name}
                                favoriteVideo={this.props.favoriteVideo}
                                unfavoriteVideo={this.props.unfavoriteVideo}
                            />
                        ))}
                        { !this.props.search.loading && favoritedVideos.length === 0 && (
                            <p>No videos match your selection</p>
                        )}
                    </div>
                </div>
            </div>
        )
    }

    renderCompanyDropdown() {

        if ( this.props.companies.length > 0 ) {
            return <div className="uk-width-1-1 uk-width-1-2@s uk-width-1-3@l">
                <div className="uk-width-1-1 uk-text-truncate" data-uk-form-custom="target: > * > span:first-child">
                    <select onChange={this.props.updateCompanyQuery}>
                        <option value="">Filter by Company...</option>
                        { this.props.companies.sort((a,b) => (a.name > b.name) ? 1 : ((b.name > a.name) ? -1 : 0)).map( company => <option key={company.id} value={company.id}>{company.name}</option> ) }
                    </select>
                    <button className="uk-button uk-button-default uk-width-1-1 uk-width-autom@l" type="button"
                            tabIndex="-1">
                        <span></span>
                        <span data-uk-icon="icon: chevron-down"></span>
                    </button>
                </div>
            </div>
        }

        return null;
    }

    renderCompanyVideoIndustryDropdown() {

        if ( this.props.companyVideoIndustries.length > 0 ) {

            return <div className="uk-width-1-1 uk-width-1-2@s uk-width-1-3@l">
                <div className="uk-width-1-1 uk-text-truncate" data-uk-form-custom="target: > * > span:first-child">
                    <select onChange={this.props.updateCompanyVideoIndustryQuery}>
                        <option value="">Filter by Industry...</option>
                        { this.props.companyVideoIndustries.sort((a,b) => (a.name > b.name) ? 1 : ((b.name > a.name) ? -1 : 0)).map( industry => <option key={industry.id} value={industry.id}>{industry.name}</option> ) }
                    </select>
                    <button className="uk-button uk-button-default uk-width-1-1 uk-width-autom@l" type="button"
                            tabIndex="-1">
                        <span></span>
                        <span data-uk-icon="icon: chevron-down"></span>
                    </button>
                </div>
            </div>
        }

        return null;
    }

    renderCareerVideoIndustryDropdown() {

        if ( this.props.careerVideoIndustries.length > 0 ) {

            return <div className="uk-width-1-1 uk-width-1-2@s uk-width-1-3@l">
                <div className="uk-width-1-1 uk-text-truncate" data-uk-form-custom="target: > * > span:first-child">
                    <select onChange={this.props.updateCareerVideoIndustryQuery}>
                        <option value="">Filter by Industry...</option>
                        { this.props.careerVideoIndustries.sort((a,b) => (a.name > b.name) ? 1 : ((b.name > a.name) ? -1 : 0)).map( industry => <option key={industry.id} value={industry.id}>{industry.name}</option> ) }
                    </select>
                    <button className="uk-button uk-button-default uk-width-1-1 uk-width-autom@l" type="button"
                            tabIndex="-1">
                        <span></span>
                        <span data-uk-icon="icon: chevron-down"></span>
                    </button>
                </div>
            </div>
        }

        return null;
    }

    getRelevantCompanyVideos () {

        return this.props.companyVideos.filter(video => {

            // Filter By Industry
            if (
                ( !video.company ) ||
                ( !video.company.primaryIndustry ) ||
                ( !!this.props.companyVideoSearch.industry && parseInt(video.company.primaryIndustry.id ) !== parseInt( this.props.companyVideoSearch.industry ) )
            ) {
                return false;
            }


            // Filter By Company
            if ( !!this.props.companyVideoSearch.company && (
                ( !video.company ) ||
                ( video.company && parseInt(video.company.id ) !== parseInt( this.props.companyVideoSearch.company ) )
            ) ) {
                return false;
            }


           // Filter By Search Term
           if( this.props.companyVideoSearch.query ) {

               // collect the primary and secondary industry names to search off of
               let primaryIndustryNames = [];
               let secondaryIndustryNames = [];
               if(video.company) {
                   let company = video.company;
                   if ( company.primaryIndustry && company.primaryIndustry.name ) {
                       primaryIndustryNames.push(company.primaryIndustry.name);
                   }

                   if(company.secondaryIndustries) {
                       // Add any non-existing secondary Industries
                       company.secondaryIndustries.forEach(secondary_industry => {
                           if ( secondary_industry.name ) {
                               secondaryIndustryNames.push(secondary_industry.name);
                           }
                       });
                   }
               }

               return (
                   video['name'] && video['name'].toLowerCase().indexOf(this.props.companyVideoSearch.query.toLowerCase() ) > -1 ||
                   video['tags'] && video['tags'].toLowerCase().indexOf(this.props.companyVideoSearch.query.toLowerCase() ) > -1 ||
                   primaryIndustryNames.length > 0 && primaryIndustryNames.join(',').toLowerCase().indexOf(this.props.companyVideoSearch.query.toLowerCase() ) > -1 ||
                   secondaryIndustryNames.length > 0 && secondaryIndustryNames.join(',').toLowerCase().indexOf(this.props.companyVideoSearch.query.toLowerCase() ) > -1
               );
           }

            return true;
        })

    }

    getRelevantCareerVideos () {

        return this.props.careerVideos.filter(video => {

            // Filter By Industry
            if(!!this.props.careerVideoSearch.industry) {
                let primaryIndustryIds = [];
                video.secondaryIndustries.forEach(secondary_industry => {
                    if(secondary_industry.primaryIndustry && secondary_industry.primaryIndustry.id) {
                        primaryIndustryIds.push(secondary_industry.primaryIndustry.id);
                    }
                });

                if(!primaryIndustryIds.includes(parseInt(this.props.careerVideoSearch.industry))) {
                    return false;
                }
            }

            // Filter By Search Term
            if( this.props.careerVideoSearch.query ) {

                // collect the primary and secondary industry names to search off of
                let primaryIndustryNames = [];
                let secondaryIndustryNames = [];

                video.secondaryIndustries.forEach(secondary_industry => {

                    if(secondary_industry.primaryIndustry && secondary_industry.primaryIndustry.name) {
                        primaryIndustryNames.push(secondary_industry.primaryIndustry.name);
                    }

                    if(secondary_industry.name) {
                        secondaryIndustryNames.push(secondary_industry.name);
                    }

                });

                return (
                    video['name'] && video['name'].toLowerCase().indexOf(this.props.careerVideoSearch.query.toLowerCase() ) > -1 ||
                    video['tags'] && video['tags'].toLowerCase().indexOf(this.props.careerVideoSearch.query.toLowerCase() ) > -1 ||
                    primaryIndustryNames.length > 0 && primaryIndustryNames.join(',').toLowerCase().indexOf(this.props.careerVideoSearch.query.toLowerCase() ) > -1 ||
                    secondaryIndustryNames.length > 0 && secondaryIndustryNames.join(',').toLowerCase().indexOf(this.props.careerVideoSearch.query.toLowerCase() ) > -1
                );
            }

            return true;
        })

    }

    getFavoritedVideos () {

        let favoritedCareerVideos = this.props.careerVideos.filter(video => {

            return video.favorite === true;

        });

        let favoritedCompanyVideos = this.props.companyVideos.filter(video => {

            return video.favorite === true;

        });

        return favoritedCareerVideos.concat(favoritedCompanyVideos);
    }

    componentDidMount() {
        this.loadVideos();
        this.props.loadUser( window.Routing.generate('logged_in_user') );
    }

    loadVideos() {
        this.props.loadVideos( window.Routing.generate('api_get_videos', {}));
    }
}

App.propTypes = {
    companies: PropTypes.array,
    industries: PropTypes.array,
    companyVideoIndustries: PropTypes.array,
    careerVideoIndustries: PropTypes.array,
    videos: PropTypes.array,
    companyVideos: PropTypes.array,
    careerVideos: PropTypes.array,
    search: PropTypes.object,
    companyVideoSearch: PropTypes.object,
    careerVideoSearch: PropTypes.object,
    user: PropTypes.object
};

App.defaultProps = {
    companies: [],
    industries: [],
    companyVideoIndustries: [],
    careerVideoIndustries: [],
    companyVideos: [],
    careerVideos: [],
    search: {},
    companyVideoSearch: {},
    careerVideoSearch: {},
    user: {}
};

export const mapStateToProps = (state = {}) => ({
    companies: state.companies,
    industries: state.industries,
    companyVideos: state.companyVideos,
    careerVideos: state.careerVideos,
    companyVideoIndustries: state.companyVideoIndustries,
    careerVideoIndustries: state.careerVideoIndustries,
    search: state.search,
    companyVideoSearch: state.companyVideoSearch,
    careerVideoSearch: state.careerVideoSearch,
    user: state.user
});

export const mapDispatchToProps = dispatch => ({
    favoriteVideo: (url, videoId) => dispatch(favoriteVideo(url, videoId)),
    unfavoriteVideo: (url, videoId) => dispatch(unfavoriteVideo(url, videoId)),
    loadVideos: (url) => dispatch(loadVideos(url)),
    loadUser: (url) => dispatch(loadUser(url)),
    updateCompanyQuery: (event) => dispatch(updateCompanyQuery(event.target.value)),
    updateCompanyVideoIndustryQuery: (event) => dispatch(updateCompanyVideoIndustryQuery(event.target.value)),
    updateCompanyVideoSearchQuery: (event) => dispatch(updateCompanyVideoSearchQuery(event.target.value)),
    updateCareerVideoIndustryQuery: (event) => dispatch(updateCareerVideoIndustryQuery(event.target.value)),
    updateCareerVideoSearchQuery: (event) => dispatch(updateCareerVideoSearchQuery(event.target.value))
});

const ConnectedApp = connect(
    mapStateToProps,
    mapDispatchToProps
)(App);

export default ConnectedApp;
