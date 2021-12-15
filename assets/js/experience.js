import $ from 'jquery';
import PrimaryIndustrySelect from "./Components/PrimaryIndustrySelect";
import RegionSelect from "./Components/RegionSelect";
import RadiusSelect from "./Components/RadiusSelect";
import SchoolSelect from "./Components/SchoolSelect";

require('select2/dist/js/select2.min');
import Inputmask from "inputmask";
import VideoComponent from "./Components/VideoComponent";
import VCountdown from './Components/VCountdown.js';
import flatpickr from "flatpickr";
import Tagify from '@yaireo/tagify'
import Routing from "./Routing";
import ResourceComponent from "./Components/ResourceComponent";

$(document).ready(function () {

    var input = document.querySelector('input[name="experience[tags]"]'),
        controller,
        tagify = new Tagify(input, {
            placeholder: 'Add search keywords to your event.',
            maxItems: 10,
            editTags: false,
            maxTags: 10,
            dropdown: {
                position: "input",
                enabled: 0 // always opens dropdown when input gets focus
            },
            whitelist: [],
            callbacks: {
                add: (event) => {
                    let tagCount = event.detail.tagify.value.length;
                    $('.js-tags-count').html(`${tagCount}/10 tags.`);
                },
                remove: (event) => {
                    let tagCount = event.detail.tagify.value.length;
                    $('.js-tags-count').html(`${tagCount}/10 tags.`);
                },
                blur: (event) => {

                    if (!$('.tagify__input').hasClass('tagify__input-reset-opacity')) {
                        $('.tagify__input').addClass('tagify__input-reset-opacity')
                    }
                },
                focus: (event) => {

                    if ($('.tagify__input').hasClass('tagify__input-reset-opacity')) {
                        $('.tagify__input').removeClass('tagify__input-reset-opacity')
                    }
                },
            }
        });

    if (!$('.tagify__input').hasClass('tagify__input-reset-opacity')) {
        $('.tagify__input').addClass('tagify__input-reset-opacity')
    }

    // listen to any keystrokes which modify tagify's input
    tagify.on('input', onInput)

    function onInput(e) {
        var value = e.detail.value
        tagify.whitelist = null // reset the whitelist

        // https://developer.mozilla.org/en-US/docs/Web/API/AbortController/abort
        controller && controller.abort()
        controller = new AbortController()

        // show loading animation and hide the suggestions dropdown
        tagify.loading(true).dropdown.hide()

        let url = Routing.generate('api_tag_search', {'value': value});

        fetch(url, {signal: controller.signal})
            .then(RES => RES.json())
            .then(function (newWhitelist) {
                tagify.whitelist = newWhitelist.results // update whitelist Array in-place
                tagify.loading(false).dropdown.show(value) // render the suggestions dropdown
            })
    }

    VCountdown({
        target: '#experience_title',
        maxChars: 75
    });

    $("#experience_startDate").flatpickr({
        dateFormat: "m/d/Y",
        minDate: "today"
    });
    $("#experience_endDate").flatpickr({
        dateFormat: "m/d/Y",
        minDate: "today"
    });

    if (document.getElementById("experienceAddressSearch")) {

        let addressSearchAutocomplete = new google.maps.places.Autocomplete(document.getElementById("experienceAddressSearch"), {
            componentRestrictions: {country: "us"},
            fields: ["address_components", "geometry", "icon", "name"],
            //origin: center,
            strictBounds: false,
            types: ["address"],
        });

        document.getElementById("experienceAddressSearch").onfocus = function () {
            this.removeAttribute('readonly');
        }

        addressSearchAutocomplete.addListener("place_changed", () => {

            const place = addressSearchAutocomplete.getPlace();
            let geoAddress = "";
            let country = "";

            // Get each component of the address from the place details,
            // and then fill-in the corresponding field on the form.
            // place.address_components are google.maps.GeocoderAddressComponent objects
            // which are documented at http://goo.gle/3l5i5Mr
            for (const component of place.address_components) {
                const componentType = component.types[0];

                switch (componentType) {
                    case "street_number": {
                        geoAddress = `${component.long_name} `;
                        break;
                    }

                    case "route": {
                        geoAddress += `${component.short_name}, `;
                        break;
                    }

                    case "locality": {
                        geoAddress += `${component.long_name}, `;
                        break;
                    }

                    case "administrative_area_level_1": {
                        geoAddress += `${component.short_name} `;
                        break;
                    }

                    case "postal_code": {
                        geoAddress += `${component.long_name}, `;
                        break;
                    }

                    case "country": {
                        country = `${component.short_name}`;
                        break;
                    }
                }
            }

            geoAddress += country;

            let route = Routing.generate('api_geocode', {'addressSearch': geoAddress});

            $.ajax({
                url: route,
                method: 'GET',
            }).then((data, textStatus, jqXHR) => {

                var map = new google.maps.Map(document.getElementById('map'), {
                    center: {
                        lat: data.latitude,
                        lng: data.longitude
                    },
                    disableDefaultUI: true,
                    panControl: false,
                    streetViewControl: false,
                    scaleControl: true,
                    zoomControl: true,
                    zoom: 12,
                    mapTypeId: 'roadmap'
                });

                let marker = new google.maps.Marker({
                    position: {lat: parseFloat(data.latitude), lng: parseFloat(data.longitude)},
                    map: map,
                });

                var latLng = marker.getPosition();
                map.setCenter(latLng);


                $('#map').show();

            }).catch((jqXHR) => {
                // do nothing
            });

        });

    }


    let latitude = $('#map').attr('data-latitude')
    let longitude = $('#map').attr('data-longitude')

    if (typeof latitude !== 'undefined' && latitude !== false &&
        typeof longitude !== 'undefined' && longitude !== false
        && latitude !== "" && longitude !== "") {

        var map = new google.maps.Map(document.getElementById('map'), {
            center: {
                lat: latitude,
                lng: longitude
            },
            disableDefaultUI: true,
            panControl: false,
            streetViewControl: false,
            scaleControl: true,
            zoomControl: true,
            zoom: 12,
            mapTypeId: 'roadmap'
        });

        let marker = new google.maps.Marker({
            position: {lat: parseFloat(latitude), lng: parseFloat(longitude)},
            map: map,
        });

        var latLng = marker.getPosition();
        map.setCenter(latLng);

        $('#map').show();
    }


    new ResourceComponent($('.js-resource-component'), window.globalEventDispatcher);
});