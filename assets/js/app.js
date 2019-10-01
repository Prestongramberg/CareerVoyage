'use strict';

// App SCSS
import * as actionTypes from "./react/root/events-calendar/actions/actionTypes";

require('../css/app.scss');

import EventDispatcher from "./EventDispatcher";
import UIkit from 'uikit';
import Icons from 'uikit/dist/js/uikit-icons';
import {deepObject} from "./react/utilities/object-utils";
import {secondsToHHMM} from "./react/utilities/string-utils";
import { saveAs } from 'file-saver';

// loads the Icon plugin
UIkit.use(Icons);

// App Vendor JS
const $ = require('jquery');
const moment = require('./vendor/moment.js');
require('./vendor/jquery-datetimepicker.js');
require('./vendor/fontawesome.js');
const ics = require('./vendor/ics.js');

// Binds to Window
window.globalEventDispatcher = new EventDispatcher();
window.UIkit = UIkit;
window.Pintex = {
    jQuery: $,
    notification: function(message, status = null) {
        UIkit.notification({
            message: message,
            pos: 'bottom-center',
            status: status,
            timeout: 2500
        });
    },
    modal: {
        dynamic_open: function(html) {
            const $modal = $('#global-modal');
            $modal.find('.uk-modal-body').html( html );
            UIkit.modal( $modal ).show();
        },
        close: function() {
            const $modal = $('#global-modal');
            UIkit.modal( $modal ).hide();
        },
        target: "#global-modal"
    },
    openCalendarEventDetails: function( event ) {

        let eventHtml = "";
        const eventPayload = deepObject(event, 'extendedProps.customEventPayload') || { className: "default" };
        const eventStartDate = moment( eventPayload.startDateAndTime , moment.ISO_8601 ).add(5, 'hours').unix(); // CST Timezone Offset
        const eventEndDate = moment( eventPayload.endDateAndTime , moment.ISO_8601 ).add(5, 'hours').unix();  // CST Timezone Offset
        const eventTitle = eventPayload.title;
        const eventDescription = eventPayload.briefDescription;
        const eventState = eventPayload.state || {};
        const eventLocation = `${eventPayload.street}, ${eventPayload.city}, ${eventState.abbreviation}, ${eventPayload.zipcode}`;

        eventHtml += `
                <h2>${eventPayload.title}</h2>
                <p>
                    <strong>About the Event</strong><br />
                    ${eventPayload.about}
                </p>
            `;

        if ( event.url ) {
            eventHtml += `<a href="${event.url}" class="uk-button uk-button-primary uk-button-small uk-margin-small-right">View More Details</a>`;
        }

        eventHtml += this.generateAddToCalendarButton( eventStartDate, eventEndDate, eventTitle, eventDescription, eventLocation );

        this.modal.dynamic_open(`
            <div class="event-modal-details">
                <div class="event-modal-details__event-info">
                    ${eventHtml}
                </div>
            </div>
        `);
    },
    generateAddToCalendarButton: function( epochStartTime, epochEndTime, title = '', description = '', location = '' ) {

        // Get the dates in Epoch to the format we need
        const startISOtoSeconds = moment.unix(epochStartTime).utc().format("YYYYMMDDTHHmmss");
        const endISOtoSeconds = moment.unix(epochEndTime).utc().format("YYYYMMDDTHHmmss");

        // Encode all our user inputs
        title = encodeURI( title );
        description = encodeURI( description );
        location = encodeURI( location );

        // HHMM format for Yahoo Duration
        const yahooDuration = secondsToHHMM( Math.min(epochEndTime - epochStartTime, 356459 ) );

        return `<div class="atc-wrapper">
            <label for="atc-checkbox" class="atc-checkbox-label">Add to Calendar</label>
            <input name="atc-checkbox" class="atc-checkbox" id="atc-checkbox" type="checkbox">
            <div class="atc-links-wrapper">
                <a class="atc-link icon-google" target="_blank" href="https://www.google.com/calendar/render?action=TEMPLATE&amp;text=${title}&amp;dates=${startISOtoSeconds}Z/${endISOtoSeconds}Z&amp;details=${description}&amp;location=${location}&amp;sprop=&amp;sprop=name:">Google Calendar</a>
                <a class="atc-link icon-yahoo" target="_blank" href="http://calendar.yahoo.com/?v=60&amp;view=d&amp;type=20&amp;title=${title}&amp;st=${startISOtoSeconds}Z&amp;dur=${yahooDuration}&amp;desc=${description}&amp;in_loc=${location}">Yahoo! Calendar</a>
                <a class="atc-link icon-ical" onClick="window.Pintex.downloadICSCalendarEvent('${title}', '${description}', '${location}', ${epochStartTime*1000}, ${epochEndTime*1000})">iCal Calendar</a>
                <a class="atc-link icon-outlook" onClick="window.Pintex.downloadICSCalendarEvent('${title}', '${description}', '${location}', ${epochStartTime*1000}, ${epochEndTime*1000})">Outlook Calendar</a>
            </div>
        </div>`;
    },
    downloadICSCalendarEvent: function( title, description, location, begin, end ) {
        const cal = ics();
        cal.addEvent(title, description, location, begin, end);
        cal.download("addToCalendar", undefined );
    }
};

// React
require('./react/root');

// Custom
require('./Custom');
