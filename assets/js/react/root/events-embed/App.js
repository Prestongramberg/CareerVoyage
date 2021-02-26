import React from "react"
import { connect } from "react-redux"
import PropTypes from "prop-types";
import FullCalendar from '@fullcalendar/react'
import dayGridPlugin from '@fullcalendar/daygrid'
import {loadEvents, radiusChanged, updateEventTypeQuery, updatePrimaryIndustryQuery, updateSecondaryIndustryQuery, updateSearchQuery, zipcodeChanged} from "./actions/actionCreators";
import Loader from "../../components/Loader/Loader"
import Pusher from "pusher-js";

class App extends React.Component {

    constructor() {
        super();
        const methods = ["getEventObjectByType", "getRelevantEvents", "handleTabNavigation", "loadEvents", "renderCalendar", "renderEventTypes", "renderIndustryDropdown"];
        methods.forEach(method => (this[method] = this[method].bind(this)));
    }

    render() {
        return this.props.calendar.loading ? (
            <div className="uk-width-1-1 uk-align-center">
                <Loader />
            </div>
        ) : this.renderCalendar();
    }

    renderCalendar() {

        const events = this.getRelevantEvents();
        const calendarEvents = events.map(event => this.getEventObjectByType( event ));
        const ranges = [ 25, 50, 70, 150 ];

        return (

            [
                <div className="pintex-calendar pintex-testing">
                    <div className="uk-grid-small uk-flex-middle" data-uk-grid>
                        <div className="uk-width-1-1 uk-width-1-1@s uk-width-1-3@l">
                            <div className="uk-search uk-search-default uk-width-1-1">
                                <span data-uk-search-icon></span>
                                <input className="uk-search-input" type="search" placeholder="Search..." onChange={this.props.updateSearchQuery} value={this.props.search.query} />
                            </div>
                        </div>
                        { this.renderIndustryDropdown() }
                        { this.props.search.industry && this.renderSecondaryIndustryDropdown() }
                        { this.renderEventTypes() }
                    </div>
                    <div className="uk-grid-small uk-flex-middle uk-margin" data-uk-grid>
                        <div className="uk-width-1-1 uk-width-1-1@s uk-width-1-3@l">
                            <div className="uk-search uk-search-default uk-width-1-1">
                                <span data-uk-search-icon></span>
                                <input className="uk-search-input" type="search" placeholder="Enter Zip Code..." onChange={(e) => { this.props.zipcodeChanged( e.target.value ) }} value={ this.props.search.zipcode } />
                            </div>
                        </div>
                        <div className="uk-width-1-1 uk-width-1-1@s uk-width-1-3@l">
                            <select className="uk-select" onChange={(e) => { this.props.radiusChanged( e.target.value ) }} >
                                <option value="">Filter by Radius...</option>
                                {ranges.map( (range, i) => <option key={i} value={range}>{range} miles</option> )}
                            </select>
                        </div>
                        <div className="uk-width-1-1 uk-width-1-1@s uk-width-1-3@l">
                            <div className="uk-button uk-button-primary" onClick={this.loadEvents}>Apply</div>
                        </div>
                    </div>
                    <div className="uk-margin">
                        <FullCalendar
                            defaultView="dayGridMonth"
                            eventLimit={true}
                            events={calendarEvents}
                            eventClick={(info) => {
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

        if ( this.props.industries.length > 0 ) {
            return <div className="uk-width-1-1 uk-width-1-2@s uk-width-1-3@l">
                <div className="uk-width-1-1 uk-text-truncate" data-uk-form-custom="target: > * > span:first-child">
                    <select onChange={this.props.updatePrimaryIndustryQuery}>
                        <option value="">Filter by Industry...</option>
                        { this.props.industries.sort((a,b) => (a.name > b.name) ? 1 : ((b.name > a.name) ? -1 : 0)).map( industry => <option key={industry.id} value={industry.id}>{industry.name}</option> ) }
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

        if ( this.props.industries.length > 0 ) {

            const secondaryIndustries = this.props.industries.map( industry => {
                return parseInt(this.props.search.industry ) === parseInt( industry.id ) ? industry.secondaryIndustries : [];
            } ).reduce((a, b) => a.concat(b), []).filter((v,i,a)=>a.findIndex((t)=>(t.id === v.id))===i);

            return <div className="uk-width-1-1 uk-width-1-2@s uk-width-1-3@l">
                <div className="uk-width-1-1 uk-text-truncate" data-uk-form-custom="target: > * > span:first-child">
                    <select onChange={this.props.updateSecondaryIndustryQuery}>
                        <option value="">Filter by Career...</option>
                        { secondaryIndustries.map( industry => <option key={industry.id} value={industry.id}>{industry.name}</option> ) }
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

        if ( this.props.events.length > 0 ) {

            const companyEventTypes = this.props.events.map( event => {
                if ( !event.friendlyEventName ) {
                    return null
                }

                if(event.className !== 'CompanyExperience') {
                    return null;
                }

                return event.friendlyEventName;

            } ).filter((v,i,a)=>a.indexOf(v)==i).filter(Boolean);


            const schoolEventTypes = this.props.events.map( event => {
                if ( !event.friendlyEventName ) {
                    return null
                }

                if(event.className !== 'SchoolExperience') {
                    return null;
                }

                return event.friendlyEventName;

            } ).filter((v,i,a)=>a.indexOf(v)==i).filter(Boolean);

            return <div className="uk-width-1-1 uk-width-1-2@s uk-width-1-3@l">
                <div className="uk-width-1-1 uk-text-truncate" data-uk-form-custom="target: > * > span:first-child">
                    <select onChange={this.props.updateEventTypeQuery}>
                        <option value="">Filter by Experience Type...</option>
                        <optgroup label="Company Experiences">
                            { companyEventTypes.sort((a,b) => (a > b) ? 1 : ((b > a) ? -1 : 0)).map( eventType => <option key={eventType} value={eventType}>{eventType}</option> ) }
                        </optgroup>
                        <optgroup label="School Experiences">
                            { schoolEventTypes.sort((a,b) => (a > b) ? 1 : ((b > a) ? -1 : 0)).map( eventType => <option key={eventType} value={eventType}>{eventType}</option> ) }
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
                ( !!this.props.search.industry && !event.secondaryIndustries ) ||
                ( !!this.props.search.industry && event.secondaryIndustries.filter(secondaryIndustry => secondaryIndustry.primaryIndustry && parseInt( secondaryIndustry.primaryIndustry.id ) === parseInt( this.props.search.industry ) ).length === 0 )
            ) {
                return false;
            }

            // Filter By Sub Industry
            if ( !!this.props.search.secondaryIndustry && event.secondaryIndustries.filter(secondaryIndustry => parseInt( secondaryIndustry.id ) === parseInt( this.props.search.secondaryIndustry ) ).length === 0 ) {
                return false;
            }

            // Filter by Event Type
            if ( !!this.props.search.eventType && ( !event.friendlyEventName || event.friendlyEventName.search(this.props.search.eventType) === -1 ) ) {
                return false;
            }

            // Filter By Search Term
            if( this.props.search.query ) {
                // basic search fields
                const basicSearchFieldsFound = searchableFields.some((field) => ( event[field] && event[field].toLowerCase().indexOf(this.props.search.query.toLowerCase() ) > -1 ) )

                // Event Type (Job Shadow, Interview, etc)
                const eventTypeFound = event['type'] && event['type']['name'].toLowerCase().indexOf(this.props.search.query.toLowerCase() ) > -1

                // Event Industry (Carpentry, Brick Layer, etc)
                const eventIndustryFound = event['secondaryIndustries'] && event['secondaryIndustries'].some((field) => ( field.name.toLowerCase().indexOf(this.props.search.query.toLowerCase() ) > -1 ) )

                return basicSearchFieldsFound || eventTypeFound || eventIndustryFound
            }

            return true;
        });
    }

    getEventObjectByType( event ) {

        const defaults = {
            customEventPayload: event,
            title: event.title,
            start: event.startDateAndTime,
            end: event.endDateAndTime
        }

        console.log(defaults);

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
        this.loadEvents();
        window.addEventListener('uk-tab-clicked', this.handleTabNavigation )
    }

    loadEvents() {
        this.props.loadEvents( window.Routing.generate('get_experiences_by_radius', {
            'radius': this.props.search.radius,
            'schoolId': this.props.schoolId,
            'userId': this.props.userId,
            'zipcode': this.props.search.zipcode
        }) );
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
    schoolId: 0,
    userId: 0
};

export const mapStateToProps = (state = {}) => ({
    calendar: state.calendar,
    events: state.events,
    industries: state.industries,
    search: state.search
});

export const mapDispatchToProps = dispatch => ({
    loadEvents: (url) => dispatch(loadEvents(url)),
    radiusChanged: (radius) => dispatch(radiusChanged(radius)),
    updateEventTypeQuery: (event) => dispatch(updateEventTypeQuery(event.target.value)),
    updatePrimaryIndustryQuery: (event) => dispatch(updatePrimaryIndustryQuery(event.target.value)),
    updateSearchQuery: (event) => dispatch(updateSearchQuery(event.target.value)),
    updateSecondaryIndustryQuery: (event) => dispatch(updateSecondaryIndustryQuery(event.target.value)),
    zipcodeChanged: (zipcode) => dispatch(zipcodeChanged(zipcode))
});

const ConnectedApp = connect(mapStateToProps, mapDispatchToProps)(App);
export default ConnectedApp;
