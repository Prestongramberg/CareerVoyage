import React from "react"
import { connect } from "react-redux"
import PropTypes from "prop-types";
import FullCalendar from '@fullcalendar/react'
import dayGridPlugin from '@fullcalendar/daygrid'
import {loadEvents, updatePrimaryIndustryQuery, updateSecondaryIndustryQuery, updateSearchQuery} from "./actions/actionCreators";

class App extends React.Component {

    constructor() {
        super();
        const methods = ["getEventObjectByType", "getRelevantEvents", "renderCalendar", "renderEventTypes", "renderIndustryDropdown"];
        methods.forEach(method => (this[method] = this[method].bind(this)));
    }

    render() {
        return this.props.calendar.loading ? (
            <div className="uk-width-1-1 uk-align-center">
                <div data-uk-spinner></div>
            </div>
        ) : this.renderCalendar();
    }

    renderCalendar() {

        const events = this.getRelevantEvents();
        const calendarEvents = events.map(event => this.getEventObjectByType( event ));

        return (
            <div className="pintex-calendar">
                <div className="uk-grid-small uk-flex-middle" data-uk-grid>
                    <div className="uk-width-1-1 uk-width-1-1@s uk-width-1-3@l">
                        <div className="uk-search uk-search-default uk-width-1-1">
                            <span data-uk-search-icon></span>
                            <input className="uk-search-input" type="search" placeholder="Search by Name..." onChange={this.props.updateSearchQuery} value={this.props.search.query} />
                        </div>
                    </div>
                    { this.renderIndustryDropdown() }
                    { this.props.search.industry && this.renderSecondaryIndustryDropdown() }
                    { this.renderEventTypes() }
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
        );
    }

    renderIndustryDropdown() {

        if ( this.props.industries.length > 0 ) {
            return <div className="uk-width-1-1 uk-width-1-2@s uk-width-1-3@l">
                <div className="uk-width-1-1 uk-text-truncate" data-uk-form-custom="target: > * > span:first-child">
                    <select onChange={this.props.updatePrimaryIndustryQuery}>
                        <option value="">Filter by Industry...</option>
                        { this.props.industries.map( industry => <option key={industry.id} value={industry.id}>{industry.name}</option> ) }
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

        const eventTypes = this.props.events.map(event => {

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

            // Filter By Search Term
            if( this.props.search.query ) {
                return searchableFields.some((field) => event[field] && event[field].toLowerCase().indexOf(this.props.search.query.toLowerCase() ) > -1 );
            }

            return true;
        });

        if ( 1 === 1 ) {
            return <div className="uk-width-1-1">

            </div>
        }

        return null;

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

            // Filter By Search Term
            if( this.props.search.query ) {
                return searchableFields.some((field) => event[field] && event[field].toLowerCase().indexOf(this.props.search.query.toLowerCase() ) > -1 );
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
        this.props.loadEvents( window.Routing.generate('get_experiences') );
    }
}

App.propTypes = {
    calendar: PropTypes.object,
    events: PropTypes.array,
    industries: PropTypes.array
};

App.defaultProps = {
    calendar: {},
    events: [],
    industries: [],
    search: {}
};

export const mapStateToProps = (state = {}) => ({
    calendar: state.calendar,
    events: state.events,
    industries: state.industries,
    search: state.search
});

export const mapDispatchToProps = dispatch => ({
    loadEvents: (url) => dispatch(loadEvents(url)),
    updatePrimaryIndustryQuery: (event) => dispatch(updatePrimaryIndustryQuery(event.target.value)),
    updateSearchQuery: (event) => dispatch(updateSearchQuery(event.target.value)),
    updateSecondaryIndustryQuery: (event) => dispatch(updateSecondaryIndustryQuery(event.target.value))
});

const ConnectedApp = connect(mapStateToProps, mapDispatchToProps)(App);
export default ConnectedApp;
