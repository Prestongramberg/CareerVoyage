import React from "react"
import {connect} from "react-redux"
import PropTypes from "prop-types";
import FullCalendar from '@fullcalendar/react'
import dayGridPlugin from '@fullcalendar/daygrid'
import DatePicker from "react-datepicker";
import "react-datepicker/dist/react-datepicker.css";

import {
    loadEvents,
    radiusChanged,
    updateEventTypeQuery,
    updatePrimaryIndustryQuery,
    updateSecondaryIndustryQuery,
    updateStartDateQuery,
    updateEndDateQuery,
    updateSearchQuery,
    zipcodeChanged,
    setEvents,
    eventsRefreshed
} from "./actions/actionCreators";
import Loader from "../../components/Loader/Loader"
import Pusher from "pusher-js";
import * as api from "../../utilities/api/api";
import * as actionTypes from "./actions/actionTypes";
import EventListing from "../../components/EventListing/EventListing";

class App extends React.Component {

    constructor() {
        super();
        this.element = null;
        this.zipcodeTimeout = null;
        this.searchQueryTimeout = null;

        debugger;

        const methods = [
            "getEventObjectByType",
            "handleTabNavigation",
            "loadEvents",
            "renderCalendar",
            "renderEventTypes",
            "renderIndustryDropdown",
            "refetchEvents",
            "updatePrimaryIndustryQuery",
            "updateSecondaryIndustryQuery",
            "updateEventTypeQuery",
            "updateSearchQuery",
            "updateRadiusQuery",
            "updateZipcodeQuery",
            "updateStartDateQuery",
            "updateEndDateQuery"
        ];
        methods.forEach(method => (this[method] = this[method].bind(this)));
    }

    render() {

        return (
            <div>
                {this.renderFilters()}

                {this.props.calendar.loading ? <div className="uk-width-1-1 uk-align-center">
                    <Loader/>
                </div> : this.renderCalendar()}
            </div>
        );
    }

    renderFilters() {

        debugger;
        const ranges = [25, 50, 70, 150];

        return (
            [
                <ul className="uk-tab" uk-tab>
                    <li><a href={Routing.generate('experience_index')}>Calendar</a></li>
                    <li className="uk-active"><a href={Routing.generate('experience_list')}>Upcoming Experiences</a>
                    </li>
                </ul>,
                <div>
                    <div className="uk-grid-small uk-flex-middle" data-uk-grid>
                        <div className="uk-width-1-1 uk-width-1-1@s uk-width-1-3@l">
                            <div className="uk-search uk-search-default uk-width-1-1">
                                <span data-uk-search-icon></span>
                                <input className="uk-search-input" type="search" value={this.props.search.searchQuery}
                                       placeholder="Search..."
                                       onChange={this.updateSearchQuery}/>
                            </div>
                        </div>
                        {this.renderIndustryDropdown()}
                        {this.props.search.industry && this.renderSecondaryIndustryDropdown()}
                        {this.renderEventTypes()}
                        <div className="uk-width-1-1 uk-width-1-1@s uk-width-1-3@l">
                            <div className="uk-search uk-search-default uk-width-1-1">
                                <span data-uk-search-icon></span>
                                <input className="uk-search-input" type="search" placeholder="Enter Zip Code..."
                                       onChange={(e) => {
                                           this.updateZipcodeQuery(e.target.value)
                                       }} value={this.props.search.zipcode}/>
                            </div>
                        </div>
                        <div className="uk-width-1-1 uk-width-1-1@s uk-width-1-3@l">
                            <select className="uk-select" onChange={(e) => {
                                this.updateRadiusQuery(e.target.value)
                            }}>
                                <option value="">Filter by Radius...</option>
                                {ranges.map((range, i) => <option key={i} value={range}
                                                                  selected={this.props.search.radius === range ? 'selected' : ''}>{range} miles</option>)}
                            </select>
                        </div>
                    </div>

                    <div className="uk-grid-small uk-flex-middle uk-margin" data-uk-grid>
                        <div className="uk-width-1-1 uk-width-1-1@s uk-width-1-3@l">
                            <DatePicker
                                className={"uk-input"}
                                selected={this.props.filters.startDate}
                                onChange={this.updateStartDateQuery}
                                popperPlacement={"bottom"}
                            />
                        </div>
                        <div className="uk-width-1-1 uk-width-1-1@s uk-width-1-3@l">
                            <DatePicker
                                className={"uk-input"}
                                selected={this.props.filters.endDate}
                                onChange={this.updateEndDateQuery}
                                popperPlacement={"bottom"}
                            />
                        </div>
                        <div className="uk-width-1-1 uk-width-1-1@s uk-width-1-3@l">
                            <a className={"uk-button uk-button-default"} href={window.Routing.generate("experience_list_new")}>Reset Filters</a>
                        </div>
                    </div>
                </div>
            ]);


    }

    renderCalendar() {

        debugger;

        let events = this.props.events;

        return (

            <div className="educator-listings uk-margin" data-uk-grid="masonry: true">
                {events.map(event => {

                    return <div className="uk-width-1-1 uk-width-1-2@l" key={event.id}>
                        <EventListing
                            title={event.title}
                            key={event.id}
                            id={event.id}
                            briefDescription={event.briefDescription}
                            className={event.className}
                            friendlyStartDateAndTime={event.friendlyStartDateAndTime}
                            friendlyEndDateAndTime={event.friendlyEndDateAndTime}
                            friendlyName={event.eventType}
                            experienceListTitle={event.schoolName || event.companyName || "N/A"}
                        />
                    </div>

                })}
                {events.length === 0 && (
                    <p>No experiences match your selection</p>
                )}
            </div>

        );
    }

    renderIndustryDropdown() {

        if (this.props.filters.industries.length > 0) {
            return <div className="uk-width-1-1 uk-width-1-2@s uk-width-1-3@l">
                <div className="uk-width-1-1 uk-text-truncate" data-uk-form-custom="target: > * > span:first-child">
                    <select onChange={this.updatePrimaryIndustryQuery}>
                        <option value="">Filter by Industry...</option>
                        {this.props.filters.industries.sort((a, b) => (a.name > b.name) ? 1 : ((b.name > a.name) ? -1 : 0)).map(industry =>
                            <option key={industry.id} value={industry.id}>{industry.name}</option>)}
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

    renderSecondaryIndustryDropdown() {

        if (this.props.filters.secondaryIndustries.length > 0) {

            return <div className="uk-width-1-1 uk-width-1-2@s uk-width-1-3@l">
                <div className="uk-width-1-1 uk-text-truncate" data-uk-form-custom="target: > * > span:first-child">
                    <select onChange={this.updateSecondaryIndustryQuery}>
                        <option value="">Filter by Career...</option>
                        {this.props.filters.secondaryIndustries.map(industry => <option key={industry.id}
                                                                                        value={industry.id}>{industry.name}</option>)}
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

    renderEventTypes() {

        debugger;

        return <div className="uk-width-1-1 uk-width-1-2@s uk-width-1-3@l">
            <div className="uk-width-1-1 uk-text-truncate" data-uk-form-custom="target: > * > span:first-child">
                <select onChange={this.updateEventTypeQuery}>
                    <option value="">Filter by Event Type...</option>
                    <optgroup label="Company Events">
                        {
                            this.props.filters.eventTypes.companyEvents.map(function (event) {
                                return (
                                    <option key={event.name}
                                            value={event.id}>{event.name}</option>
                                )
                            })
                        }
                    </optgroup>
                    <optgroup label="School Events">
                        {
                            this.props.filters.eventTypes.schoolEvents.map(function (event) {
                                return (
                                    <option key={event.name}
                                            value={event.id}>{event.name}</option>
                                )
                            })
                        }
                    </optgroup>

                </select>
                <button className="uk-button uk-button-default uk-width-1-1 uk-width-autom@l" type="button"
                        tabIndex="-1">
                    <span></span>
                    <span data-uk-icon="icon: chevron-down"></span>
                </button>
            </div>
        </div>
    }

    handleTabNavigation() {
        this.forceUpdate();
    }

    getEventObjectByType(event) {

        const defaults = {
            customEventPayload: event,
            title: event.title,
            start: event.startDateAndTime,
            end: event.endDateAndTime
        }

        switch (event.className) {
            case "CompanyExperience":
                return {
                    ...defaults,
                    color: "#0072B1",
                    url: window.Routing.generate("experience_view", {'id': event.id})
                }
            case "SchoolExperience":
                return {
                    ...defaults,
                    color: "#FFC82C",
                    url: window.Routing.generate("experience_view", {'id': event.id})
                }
            default:
                return defaults
        }
    }


    componentDidMount() {
        window.addEventListener('uk-tab-clicked', this.handleTabNavigation)
        debugger;
        this.loadEvents();
    }

    /**
     * We Should have this method called when the pagination is clicked and on load that way you can
     * set the startDate and endDate, etc in the state.
     */
    loadEvents(queryParams = {}) {

        debugger;

        let search = {
            ...this.props.search,
            ...queryParams
        };

        //search.start = this.element.getApi().state.dateProfile.currentRange.start.toLocaleDateString("en-US");
        //search.end = this.element.getApi().state.dateProfile.currentRange.end.toLocaleDateString("en-US");

        let url = window.Routing.generate('get_experiences_by_radius', search);

        this.props.loadEvents(url);
    }

    refetchEvents() {
        //let calendarApi = this.element.getApi();
        //calendarApi.refetchEvents();
    }

    updatePrimaryIndustryQuery(event) {
        this.props.updatePrimaryIndustryQuery(event);
        this.loadEvents({industry: event.target.value});
    }

    updateSecondaryIndustryQuery(event) {
        this.props.updateSecondaryIndustryQuery(event);
        this.loadEvents({secondaryIndustry: event.target.value});
    }

    updateEventTypeQuery(event) {
        this.props.updateEventTypeQuery(event);
        this.loadEvents({eventType: event.target.value});
    }

    updateStartDateQuery(date) {

        debugger;
        this.props.updateStartDateQuery(date);
        this.loadEvents({start: date.toLocaleDateString("en-US")});
    }

    updateEndDateQuery(date) {

        debugger;
        this.props.updateEndDateQuery(date);
        this.loadEvents({end: date.toLocaleDateString("en-US")});
    }

    updateZipcodeQuery(zipcode) {

        clearTimeout(this.zipcodeTimeout);

        this.props.zipcodeChanged(zipcode);

        // Make a new timeout set to go off in 800ms
        this.zipcodeTimeout = setTimeout(() => {
            this.loadEvents({zipcode: zipcode});
        }, 1000);
    }

    updateRadiusQuery(radius) {
        this.props.radiusChanged(radius);
        this.loadEvents({radius: radius});
    }

    updateSearchQuery(event) {

        debugger;
        clearTimeout(this.searchQueryTimeout);

        let searchValue = event.target.value;
        this.props.updateSearchQuery(searchValue);

        // Make a new timeout set to go off in 800ms
        this.searchQueryTimeout = setTimeout(() => {
            this.loadEvents({searchQuery: searchValue});
        }, 1000);
    }
}

App.propTypes = {
    calendar: PropTypes.object,
    events: PropTypes.array,
    industries: PropTypes.array,
    schoolId: PropTypes.number,
    userId: PropTypes.number
};

App.defaultProps = {
    calendar: {},
    events: [],
    industries: [],
    search: {},
    filters: {},
    schoolId: 0,
    userId: 0
};

export const mapStateToProps = (state = {}) => ({
    calendar: state.calendar,
    events: state.events,
    industries: state.industries,
    search: state.search,
    filters: state.filters,
});

export const mapDispatchToProps = dispatch => ({
    loadEvents: (url) => dispatch(loadEvents(url)),
    setEvents: (events) => dispatch(setEvents(events)),
    eventsRefreshed: () => dispatch(eventsRefreshed()),
    radiusChanged: (radius) => dispatch(radiusChanged(radius)),
    updateEventTypeQuery: (event) => dispatch(updateEventTypeQuery(event.target.value)),
    updatePrimaryIndustryQuery: (event) => dispatch(updatePrimaryIndustryQuery(event.target.value)),
    updateSearchQuery: (searchValue) => dispatch(updateSearchQuery(searchValue)),
    updateSecondaryIndustryQuery: (event) => dispatch(updateSecondaryIndustryQuery(event.target.value)),
    updateStartDateQuery: (date) => dispatch(updateStartDateQuery(date)),
    updateEndDateQuery: (date) => dispatch(updateEndDateQuery(date)),
    zipcodeChanged: (zipcode) => dispatch(zipcodeChanged(zipcode))
});

const ConnectedApp = connect(mapStateToProps, mapDispatchToProps)(App);
export default ConnectedApp;
