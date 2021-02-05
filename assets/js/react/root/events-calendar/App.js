import React from "react"
import {connect} from "react-redux"
import PropTypes from "prop-types";
import FullCalendar from '@fullcalendar/react'
import dayGridPlugin from '@fullcalendar/daygrid'
import {
    loadEvents,
    radiusChanged,
    updateEventTypeQuery,
    updatePrimaryIndustryQuery,
    updateSecondaryIndustryQuery,
    updateSearchQuery,
    zipcodeChanged,
    setEvents,
    eventsRefreshed
} from "./actions/actionCreators";
import Loader from "../../components/Loader/Loader"
import Pusher from "pusher-js";
import * as api from "../../utilities/api/api";
import * as actionTypes from "./actions/actionTypes";

class App extends React.Component {

    constructor() {
        super();
        this.element = null;
        this.timeout = null;
        const methods = [
            "getEventObjectByType",
            "getRelevantEvents",
            "handleTabNavigation",
            "loadEvents",
            "renderCalendar",
            "renderEventTypes",
            "renderIndustryDropdown",
            "getEvents",
            "refetchEvents",
            "updatePrimaryIndustryQuery",
            "updateSecondaryIndustryQuery",
            "updateEventTypeQuery",
            "updateSearchQuery"
        ];
        methods.forEach(method => (this[method] = this[method].bind(this)));
    }

    render() {
        return this.props.calendar.loading ? (
            <div className="uk-width-1-1 uk-align-center">
                <Loader/>
            </div>
        ) : this.renderCalendar();
    }

    renderCalendar() {

        debugger;

        const ranges = [25, 50, 70, 150];

        if (this.props.search.refetchEvents) {
            this.refetchEvents();
            this.props.eventsRefreshed();
        }

        return (

            [
                <ul className="uk-tab" uk-tab>
                    <li className="uk-active"><a href={Routing.generate('experience_index')}>Calendar</a></li>
                    <li><a href={Routing.generate('experience_list')}>Upcoming Experiences</a></li>
                </ul>,

                <div className="pintex-calendar pintex-testing">
                    <div className="uk-grid-small uk-flex-middle" data-uk-grid>
                        <div className="uk-width-1-1 uk-width-1-1@s uk-width-1-3@l">
                            <div className="uk-search uk-search-default uk-width-1-1">
                                <span data-uk-search-icon></span>
                                <input className="uk-search-input" type="search" placeholder="Search..."
                                       onChange={this.updateSearchQuery}/>
                            </div>
                        </div>
                        {this.renderIndustryDropdown()}
                        {this.props.search.industry && this.renderSecondaryIndustryDropdown()}
                        {this.renderEventTypes()}
                    </div>
                    <div className="uk-grid-small uk-flex-middle uk-margin" data-uk-grid>
                        <div className="uk-width-1-1 uk-width-1-1@s uk-width-1-3@l">
                            <div className="uk-search uk-search-default uk-width-1-1">
                                <span data-uk-search-icon></span>
                                <input className="uk-search-input" type="search" placeholder="Enter Zip Code..."
                                       onChange={(e) => {
                                           this.props.zipcodeChanged(e.target.value)
                                       }} value={this.props.search.zipcode}/>
                            </div>
                        </div>
                        <div className="uk-width-1-1 uk-width-1-1@s uk-width-1-3@l">
                            <select className="uk-select" onChange={(e) => {
                                this.props.radiusChanged(e.target.value)
                            }}>
                                <option value="">Filter by Radius...</option>
                                {ranges.map((range, i) => <option key={i} value={range}>{range} miles</option>)}
                            </select>
                        </div>
                        {/*<div className="uk-width-1-1 uk-width-1-1@s uk-width-1-3@l">
                            <div className="uk-button uk-button-primary" onClick={this.refetchEvents}>Apply</div>
                        </div>*/}
                    </div>
                    <div className="uk-margin">
                        <FullCalendar
                            ref={el => this.element = el}
                            defaultView="dayGridMonth"
                            eventLimit={true}
                            events={this.getEvents}
                            eventClick={(info) => {
                                debugger;
                                info.jsEvent.preventDefault(); // don't let the browser navigate
                                window.Pintex.openCalendarEventDetails(info.event)
                            }}
                            header={{
                                left: 'prev,next',
                                center: 'title',
                                right: 'dayGridDay,dayGridWeek,dayGridMonth'
                            }}
                            plugins={[dayGridPlugin]}
                            timeZone={'America/Chicago'}
                        />
                    </div>
                </div>
            ]

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

    getRelevantEvents() {
        return this.props.events.filter(event => {

            // Set Searchable Fields
            const searchableFields = ["title"];

            // Filter By Industry
            if (
                (!!this.props.search.industry && !event.secondaryIndustries) ||
                (!!this.props.search.industry && event.secondaryIndustries.filter(secondaryIndustry => secondaryIndustry.primaryIndustry && parseInt(secondaryIndustry.primaryIndustry.id) === parseInt(this.props.search.industry)).length === 0)
            ) {
                return false;
            }

            // Filter By Sub Industry
            if (!!this.props.search.secondaryIndustry && event.secondaryIndustries.filter(secondaryIndustry => parseInt(secondaryIndustry.id) === parseInt(this.props.search.secondaryIndustry)).length === 0) {
                return false;
            }

            // Filter by Event Type
            if (!!this.props.search.eventType && (!event.friendlyEventName || event.friendlyEventName.search(this.props.search.eventType) === -1)) {
                return false;
            }

            // Filter By Search Term
            if (this.props.search.query) {
                // basic search fields
                const basicSearchFieldsFound = searchableFields.some((field) => (event[field] && event[field].toLowerCase().indexOf(this.props.search.query.toLowerCase()) > -1))

                // Event Type (Job Shadow, Interview, etc)
                const eventTypeFound = event['type'] && event['type']['name'].toLowerCase().indexOf(this.props.search.query.toLowerCase()) > -1

                // Event Industry (Carpentry, Brick Layer, etc)
                const eventIndustryFound = event['secondaryIndustries'] && event['secondaryIndustries'].some((field) => (field.name.toLowerCase().indexOf(this.props.search.query.toLowerCase()) > -1))

                return basicSearchFieldsFound || eventTypeFound || eventIndustryFound
            }

            return true;
        });
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
                    url: window.Routing.generate("company_experience_view", {'id': event.id})
                }
            case "SchoolExperience":
                return {
                    ...defaults,
                    color: "#FFC82C",
                    url: window.Routing.generate("school_experience_view", {'id': event.id})
                }
            default:
                return defaults
        }
    }


    componentDidMount() {
        window.addEventListener('uk-tab-clicked', this.handleTabNavigation)
    }

    /**
     * We Should have this method called when the pagination is clicked and on load that way you can
     * set the startDate and endDate, etc in the state.
     *
     *
     *
     * @param startDate
     * @param endDate
     * @param successCallback
     * @param failureCallback
     */
    loadEvents(startDate, endDate, successCallback, failureCallback) {

        debugger;

        let url = window.Routing.generate('get_experiences_by_radius', {
            'radius': this.props.search.radius,
            'schoolId': this.props.schoolId,
            'userId': this.props.userId,
            'zipcode': this.props.search.zipcode,
            'start': startDate,
            'end': endDate,
            'searchQuery': this.props.search.query,
            'industry': this.props.search.industry,
            'secondaryIndustry': this.props.search.secondaryIndustry,
            'eventType': this.props.search.eventType
        });

        api.get(url)
            .then((response) => {
                if (response.statusCode < 300) {
                    let events = response.responseBody.data;
                    const calendarEvents = events.map(event => this.getEventObjectByType(event));
                    successCallback(calendarEvents);

                    this.props.setEvents(response.responseBody);
                } else {
                }
            })
            .catch(() => {
            })

        /*  this.props.loadEvents(window.Routing.generate('get_experiences_by_radius', {
              'radius': this.props.search.radius,
              'schoolId': this.props.schoolId,
              'userId': this.props.userId,
              'zipcode': this.props.search.zipcode,
              'start': startDate,
              'end': endDate
          }));*/
    }

    getEvents(fetchInfo, successCallback, failureCallback) {
        this.loadEvents(fetchInfo.startStr, fetchInfo.endStr, successCallback, failureCallback);
    }

    refetchEvents() {
        let calendarApi = this.element.getApi();
        calendarApi.refetchEvents();
    }

    updatePrimaryIndustryQuery(event) {
        this.props.updatePrimaryIndustryQuery(event);
    }

    updateSecondaryIndustryQuery(event) {
        this.props.updateSecondaryIndustryQuery(event);
    }

    updateEventTypeQuery(event) {
        this.props.updateEventTypeQuery(event);
    }

    updateSearchQuery(event) {

        debugger;
        clearTimeout(this.timeout);

        let searchValue = event.target.value;
        // Make a new timeout set to go off in 800ms
        this.timeout = setTimeout(() => {
            this.props.updateSearchQuery(searchValue);
        }, 500);
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
    zipcodeChanged: (zipcode) => dispatch(zipcodeChanged(zipcode))
});

const ConnectedApp = connect(mapStateToProps, mapDispatchToProps)(App);
export default ConnectedApp;
