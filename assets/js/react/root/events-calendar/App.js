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
    eventsRefreshed,
    startDateChanged
} from "./actions/actionCreators";
import Loader from "../../components/Loader/Loader"
import Pusher from "pusher-js";
import * as api from "../../utilities/api/api";
import * as actionTypes from "./actions/actionTypes";
import {right} from "core-js/internals/array-reduce";

class App extends React.Component {

    constructor() {
        super();
        this.element = null;
        this.zipcodeTimeout = null;
        this.searchQueryTimeout = null;
        const methods = [
            "getEventObjectByType",
            "handleTabNavigation",
            "loadEvents",
            "renderCalendar",
            "renderEventTypes",
            "renderIndustryDropdown",
            "updatePrimaryIndustryQuery",
            "updateSecondaryIndustryQuery",
            "updateEventTypeQuery",
            "updateSearchQuery",
            "updateRadiusQuery",
            "updateZipcodeQuery",
            "handleDates"
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

        const ranges = [25, 50, 70, 150];

        return (
            [
                <ul className="uk-tab" uk-tab>
                    <li className="uk-active"><a href={Routing.generate('experience_index')}>Calendar</a></li>
                    <li><a href={Routing.generate('experience_list')}>Upcoming Experiences</a></li>
                </ul>,
                <div style={{marginBottom: "30px"}}>
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

                        <div className="uk-width-1-1 uk-width-1-1@s uk-width-1-3@l">
                            <a className={"uk-button uk-button-default"}
                               href={window.Routing.generate("experience_index")}>Reset Filters</a>
                        </div>
                    </div>

                </div>
            ]);


    }

    renderCalendar() {

        debugger;

        let events = this.props.events.map(event => this.getEventObjectByType(event));

        return (

            [
                <div className="pintex-calendar pintex-testing">
                    <div className="uk-margin">
                        <FullCalendar
                            ref={el => this.element = el}
                            defaultView="dayGridMonth"
                            eventLimit={true}
                            editable={true}
                            selectable={true}
                            selectMirror={true}
                            dayMaxEvents={true}
                            datesRender={this.handleDates}
                            events={events}
                            defaultDate={this.props.filters.startDate || new Date()}
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

    handleDates(data) {

        if (this.element) {
            let start = this.element.getApi().state.dateProfile.currentRange.start.toLocaleDateString("en-US");
            let end = this.element.getApi().state.dateProfile.currentRange.end.toLocaleDateString("en-US");

            this.props.startDateChanged(this.element.getApi().state.dateProfile.currentRange.start);

            this.loadEvents({
                start: start,
                end: end
            });
        }
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

        search.start = this.element.getApi().state.dateProfile.currentRange.start.toLocaleDateString("en-US");
        search.end = this.element.getApi().state.dateProfile.currentRange.end.toLocaleDateString("en-US");

        let url = window.Routing.generate('get_experiences_by_radius', search);

        this.props.loadEvents(url);
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
    zipcodeChanged: (zipcode) => dispatch(zipcodeChanged(zipcode)),
    startDateChanged: (date) => dispatch(startDateChanged(date)),
});

const ConnectedApp = connect(mapStateToProps, mapDispatchToProps)(App);
export default ConnectedApp;
