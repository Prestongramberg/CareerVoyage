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
import swal from 'sweetalert';

// loads the Icon plugin
UIkit.use(Icons);

// App Vendor JS
const $ = require('jquery');
// const moment = require('./vendor/moment.js');
require('./vendor/jquery-datetimepicker.js');
require('./vendor/fontawesome.js');
require('./vendor/jquery-ui.min.js');
const moment = require('moment-timezone');
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

            // debugger;
            const $modal = $('#global-modal');
            $modal.find('.uk-modal-body').html( html );
            
            UIkit.modal( $modal ).show();

            const elem = document.querySelector("#modal-change-date");
            const config = {
                "attributes": true
            }
            const observer = new MutationObserver(function(mutations){
                mutations.forEach(function(mutation){
                    if(mutation.type === 'attributes') {
                        let name = elem.className;
                        if(name.includes("uk-open") ){
                            
                            $('.uk-timepicker').each(function( index ) {

                                var $elem = $(this);
                                var dropDirection = $elem.hasClass('uk-timepicker-up') ? "up" : "down";

                                $elem.daterangepicker({
                                    drops: dropDirection,
                                    singleDatePicker: true,
                                    timePicker: true,
                                    timePickerIncrement: 5,
                                    linkedCalendars: false,
                                    showCustomRangeLabel: false,
                                    locale: {
                                        format: 'MM/DD/YYYY h:mm A'
                                    }
                                }, function(start, end, label) {
                                    console.log('New date range selected: ' + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD') + ' (predefined range: ' + label + ')');
                                });
                            });
                        }
                    }
                });
            });

            observer.observe(elem, config);

        },
        close: function() {
            const $modal = $('#global-modal');
            UIkit.modal( $modal ).hide();
        },
        target: "#global-modal"
    },
    openCalendarEventDetails: function( event ) {

        debugger;

        let eventHtml = "";
        const eventPayload = deepObject(event, 'extendedProps.customEventPayload') || { className: "default" };
        const eventStartDate = parseInt( eventPayload.startDateAndTimeTimeStamp );
        const eventEndDate = parseInt( eventPayload.endDateAndTimeTimeStamp );
        const eventTitle = eventPayload.title;
        const eventAbout = eventPayload.about;
        const eventDescription = eventPayload.briefDescription;
        const eventState = eventPayload.state || {};
        const eventLocation = `${eventPayload.street}, ${eventPayload.city}, ${eventState.abbreviation}, ${eventPayload.zipcode}`;
        const eventId = parseInt(eventPayload.id);

        console.log(eventPayload);

        var this_level = this;

        debugger;

        eventHtml += `
                <button class="close-modal-button uk-button uk-button-danger uk-button-small">x</button>
                <h2>${eventPayload.title}</h2>
                <p>
                    <strong>About the Experience</strong><br />
                    ${eventAbout || eventDescription}
                </p>
            `;

        if ( event.url ) {
            eventHtml += `<a href="${event.url}" class="uk-button uk-button-primary uk-button-small uk-margin-small-right uk-margin-small-bottom" style="position: relative; z-index: 99999">View More Details</a>`;
        }

        eventHtml += this.generateAddToCalendarButton( eventStartDate, eventEndDate, eventTitle, eventDescription, eventLocation );

        if( eventPayload.className == "CompanyExperience") {
            $.post('/dashboard/companies/experiences/' + eventId + '/data', {}, function(data){
                if(data.allow_edit === true){
                    eventHtml += this_level.generateEditCancelButtons( eventId, eventStartDate, eventEndDate, 'companies', 'company_event_delete');
                }

                this_level.openModal(eventHtml);
            });
        } else if(eventPayload.className == "SchoolExperience") { 
            $.post('/dashboard/schools/experiences/' + eventId + '/data', {}, function(data){
                if(data.allow_edit === true){
                    eventHtml += this_level.generateEditCancelButtons( eventId, eventStartDate, eventEndDate, 'schools', 'school_event_delete');
                }

                this_level.openModal(eventHtml);
            });
        }else {
            this.openModal(eventHtml);
        }
    },
    generateAddToCalendarButton: function( epochStartTime, epochEndTime, title = '', description = '', location = '' ) {

        // Get the dates from CST to EPOCH
        const startISOtoSeconds = moment.unix(epochStartTime).utcOffset('+00:00').format("YYYYMMDDTHHmmss");
        const endISOtoSeconds = moment.unix(epochEndTime).utcOffset('+00:00').format("YYYYMMDDTHHmmss");


        // Encode all our user inputs
        title = encodeURI( title.trim() );
        description = encodeURI( description.trim() );
        location = encodeURI( location );

        // HHMM format for Yahoo Duration
        const yahooDuration = secondsToHHMM( Math.min(epochEndTime - epochStartTime, 356459 ) );

        return `<div class="atc-wrapper uk-margin-small-bottom">
            <label for="atc-checkbox" class="atc-checkbox-label">Add to Calendar</label>
            <input name="atc-checkbox" class="atc-checkbox" id="atc-checkbox" type="checkbox">
            <div class="atc-links-wrapper">
                <a class="atc-link icon-google" target="_blank" href="https://www.google.com/calendar/render?action=TEMPLATE&amp;text=${title}&amp;dates=${startISOtoSeconds}/${endISOtoSeconds}&amp;details=${description}&amp;location=${location}&amp;sprop=&amp;sprop=name:">Google Calendar</a>
                <a class="atc-link icon-yahoo" target="_blank" href="http://calendar.yahoo.com/?v=60&amp;view=d&amp;type=20&amp;title=${title}&amp;st=${startISOtoSeconds}&amp;dur=${yahooDuration}&amp;desc=${description}&amp;in_loc=${location}">Yahoo! Calendar</a>
                <a class="atc-link icon-ical" onClick="window.Pintex.downloadICSCalendarEvent('${title}', '${description}', '${location}', ${epochStartTime*1000}, ${epochEndTime*1000})">iCal Calendar</a>
                <a class="atc-link icon-outlook" onClick="window.Pintex.downloadICSCalendarEvent('${title}', '${description}', '${location}', ${epochStartTime*1000}, ${epochEndTime*1000})">Outlook Calendar</a>
            </div>
        </div>`;
    },
    downloadICSCalendarEvent: function( title, description, location, begin, end ) {
        const cal = ics();

        cal.addEvent(
            decodeURI(title).trim(),
            decodeURI(description).trim(),
            decodeURI(location),
            begin,
            end
        );
        cal.download("addToCalendar", undefined );
    },
    generateEditCancelButtons( eventId, epochStartTime, epochEndTime, location, deleteAction) {

        // Get the dates from CST to EPOCH
        const startISOtoSeconds = moment.unix(epochStartTime).utcOffset('+06:00').format("YYYYMMDDTHHmmss");
        const endISOtoSeconds = moment.unix(epochEndTime).utcOffset('+06:00').format("YYYYMMDDTHHmmss");

        return `
        <a class="uk-button uk-button-danger uk-button-small uk-margin-small-bottom" href="/dashboard/${location}/experiences/${eventId}/edit">Change Date</a>               
        <div id="modal-change-date" uk-modal>
            <div class="uk-modal-dialog uk-modal-body">
                <h3>Change Date of Experience</h3>
                <form class="uk-inline" action="/api/experiences/${eventId}/teach-lesson-event-change-date" method="POST">

                    <label class="uk-form-label">Start Date.</label>
                    <input class="uk-timepicker uk-input" name="newStartDate" type="text">
                    <label class="uk-form-label">End Date.</label>
                    <input class="uk-timepicker uk-input" name="newEndDate" type="text">

                    <label>Reason for changing the date:</label>
                    <textarea class="uk-textarea" name="customMessage" required style="width: 100%"></textarea>

                    <p>
                        <button class="uk-button uk-button-default uk-modal-close" type="button">Cancel</button>
                        <button class="uk-button uk-button-danger" type="submit">Submit</button>
                    </p>
                </form>
            </div>
        </div>

        
        <a class="uk-button uk-button-danger uk-button-small uk-margin-small-bottom" href="#modal-delete-experience" uk-toggle>Cancel Experience</a>
        <div id="modal-delete-experience" uk-modal>
            <div class="uk-modal-dialog uk-modal-body">
                <h3>Cancel Experience</h3>
                <form class="uk-inline" action="/api/experiences/${eventId}/${deleteAction}" method="POST">

                    <label>Reason for deleting the event:</label>
                    <textarea class="uk-textarea" name="customMessage" required style="width: 100%"></textarea>

                    <p>
                        <button class="uk-button uk-button-default uk-modal-close" type="button">Cancel</button>
                        <button class="uk-button uk-button-danger" type="submit">Submit</button>
                    </p>
                </form>
            </div>
        </div>
        `;
    },
    openModal(eventHtml) {
        this.modal.dynamic_open(`
            <div class="event-modal-details">
                <div class="event-modal-details__event-info">
                    ${eventHtml}
                </div>
            </div>
        `);
    }
};

$(document).on('click', '.close-modal-button', function(){
    UIkit.modal('#global-modal').hide();
});

$(document).on('change', '.js-primary-industry', function(){
    this.form.submit();
});


$(document).on('click', '.js-un-teach-lesson', function(){

});

$(document).on('click', '.js-teach-lesson', function(){

});


// React
require('./react/root');

// Custom
require('./Custom');

// Resource Management (files, etc)
require('./resource-management/companies');
require('./resource-management/company-experience');
require('./resource-management/lessons');
require('./resource-management/schools');
require('./resource-management/school-experience');
