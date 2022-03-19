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
window.$ = $;
// const moment = require('./vendor/moment.js');
require('./vendor/jquery-datetimepicker.js');
require('./vendor/fontawesome.js');
require('./vendor/jquery-ui.min.js');
const moment = require('moment-timezone');
const ics = require('./vendor/ics.js');
require('flatpickr/dist/flatpickr.min.css');
require('@yaireo/tagify/dist/tagify.css');

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
        const eventStartDate = parseInt( eventPayload.startDateAndTimeTimestamp );
        const eventEndDate = parseInt( eventPayload.endDateAndTimeTimestamp );
        const eventTitle = eventPayload.title;
        const eventAbout = eventPayload.about;
        const eventDescription = eventAbout;
        const eventState = eventPayload.state || {};
        const eventLocation = `${eventPayload.street}, ${eventPayload.city}, ${eventState.abbreviation}, ${eventPayload.zipcode}`;
        const eventId = parseInt(eventPayload.id);

        console.log(eventPayload);

        var this_level = this;
        eventHtml += `<button class="close-modal-button uk-button uk-button-danger uk-button-small">x</button>`;


        if(eventTitle) {
            eventHtml += `<h2>${eventPayload.title}</h2>`;
        }

        if(eventAbout) {
            eventHtml += `<p>
                    <strong>About the Experience</strong><br />
                    ${eventAbout.replace(/\<br\>/g,"").replace(/h3/g,"p").replace(/<p><\/p>/g,"").replace(/<\/?span[^>]*>/g,"")}
                </p>`;
        }

        if ( event.url ) {
            eventHtml += `<a target="_blank" href="${event.url}" class="uk-button uk-button-primary uk-button-xl uk-margin-small-right uk-width-1-1">View More Details</a>`;
        } else if (eventPayload.url) {
            eventHtml += `<a target="_blank" href="${eventPayload.url}" class="uk-button uk-button-primary uk-button-xl uk-margin-small-right uk-width-1-1">View More Details</a>`;
        }

        if(event?._def.extendedProps?.giveFeedbackUrl) {
            eventHtml += `<a target="_blank" href="${event._def.extendedProps.giveFeedbackUrl}" class="uk-button uk-button-primary uk-button-xl uk-margin-small uk-width-1-1">Give Feedback</a>`;
        }

        if(event?._def.extendedProps?.viewFeedbackUrl) {
            eventHtml += `<a target="_blank" href="${event._def.extendedProps.viewFeedbackUrl}" class="uk-button uk-button-secondary uk-button-xl uk-margin-small uk-width-1-1">View Feedback</a>`;
        }

        this.openModal(eventHtml);
    },
    generateAddToCalendarButton: function( epochStartTime, epochEndTime, title = '', description = '', location = '' ) {

        debugger;

        // Get the dates from CST to EPOCH
        const startISOtoSeconds = moment.unix(epochStartTime).format("YYYYMMDDTHHmmss");
        const endISOtoSeconds = moment.unix(epochEndTime).format("YYYYMMDDTHHmmss");


        // Encode all our user inputs
        title = encodeURI( title.trim() );
        description = encodeURI( description.trim() );
        location = encodeURI( location );

        // HHMM format for Yahoo Duration
        const yahooDuration = secondsToHHMM( Math.min(epochEndTime - epochStartTime, 356459 ) );

        return `
        <button class="uk-button uk-button-primary" type="button">Add To Calendar</button>
        <div uk-dropdown class="uk-text-left">
            <ul class="uk-nav uk-dropdown-nav">
                <li><a class="icon-google" target="_blank" href="https://www.google.com/calendar/render?action=TEMPLATE&amp;text=${title}&amp;dates=${startISOtoSeconds}/${endISOtoSeconds}&amp;details=${description}&amp;location=${location}&amp;sprop=&amp;sprop=name:">Google Calendar</a></li>
                <li><a class="icon-yahoo" target="_blank" href="http://calendar.yahoo.com/?v=60&amp;view=d&amp;type=20&amp;title=${title}&amp;st=${startISOtoSeconds}&amp;dur=${yahooDuration}&amp;desc=${description}&amp;in_loc=${location}">Yahoo! Calendar</a></li>
                <li><a class="icon-ical" onClick="window.Pintex.downloadICSCalendarEvent('${title}', '${description}', '${location}', ${epochStartTime*1000}, ${epochEndTime*1000})">iCal Calendar</a></li>
                <li><a class="icon-outlook" onClick="window.Pintex.downloadICSCalendarEvent('${title}', '${description}', '${location}', ${epochStartTime*1000}, ${epochEndTime*1000})">Outlook Calendar</a></li>
            </ul>
        </div>
`;

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
