import $ from 'jquery';
import PrimaryIndustrySelect from "./Components/PrimaryIndustrySelect";
import RegionSelect from "./Components/RegionSelect";
import RadiusSelect from "./Components/RadiusSelect";

require('select2/dist/js/select2.min');

$(document).ready(function () {
    new PrimaryIndustrySelect($('.js-form'), window.globalEventDispatcher);
    new RegionSelect($('.js-form'), window.globalEventDispatcher);
    new RadiusSelect($('.js-form'), window.globalEventDispatcher);


    $('.js-select2').select2({
        width: '100%'
    });


    $('#professional_edit_profile_form_schools').select2({
        placeholder: "Select school(s)",
        allowClear: true,
        width: '100%',
        sortResults: data => data.sort((a, b) => a.text.localeCompare(b.text))
    });

    $('#professional_edit_profile_form_regions').select2({
        placeholder: "Select region(s)",
        allowClear: true,
        width: '100%'
    });


    let countyJson = $('.js-form').attr('data-county-json')

    let polygons = [];

    if (typeof countyJson !== 'undefined' && countyJson !== false) {

        var map = new google.maps.Map(document.getElementById('map'), {
            center: {
                lat: 46.392410,
                lng: -94.636230
            },
            disableDefaultUI: true,
            panControl: false,
            streetViewControl: false,
            scaleControl: true,
            zoomControl: true,
            zoom: 6,
            mapTypeId: 'roadmap'
        });

        countyJson = JSON.parse(countyJson);

        countyJson.forEach(function (county, index) {
            var count = 0;

            let coords = county.coordinates;

            for (var i = 0; i < coords.length; i++) {
                // array for the LatLng coordinates
                var polygonCoords = [];
                // go through each coordinate in the array.
                // GeoJSON is [longitude,latitude]
                for (var j = 0; j < coords[i].length; j++) {

                    for (var k = 0; k < coords[i][j].length; k++) {

                        var pt = new google.maps.LatLng(coords[i][j][k][1], coords[i][j][k][0])
                        polygonCoords.push(pt);
                        count++;
                    }
                }


                // Construct the polygon.
                var polygon = new google.maps.Polygon({
                    paths: polygonCoords,
                    strokeColor: '#808080',
                    strokeOpacity: 0.8,
                    strokeWeight: 1,
                    fillColor: county.color,
                    fillOpacity: 0.35,
                    map: map
                });

                polygons.push(polygon);

                var infoWindow = new google.maps.InfoWindow();
                google.maps.event.addListener(polygon, 'mouseover', function (e) {

                    let info = `
                        <div>${county.region_name}</div>
                        <div>${county.service_cooperative_name}</div>
                        <div>${county.name}</div>
                    `;

                    infoWindow.setContent(info);
                    var latLng = e.latLng;
                    infoWindow.setPosition(latLng);
                    infoWindow.open(map);
                });

                google.maps.event.addListener(polygon, 'mouseout', function (e) {
                    infoWindow.close(map);
                });

            }

        });

        debugger;
        initMarkers();
    }



    var bounds = new google.maps.LatLngBounds();
    polygons.forEach(function (polygon, index) {

        polygon.getPaths().forEach(function(path, index)
        {
            var points = path.getArray();
            for(var p in points) bounds.extend(points[p]);
        });

    });

    // todo can you find the bounds for the state of minnesota? as I appear to be getting some chicago addresses back?
    const input = document.getElementById("professional_edit_profile_form_addressSearch");
    const options = {
        bounds: bounds,
        componentRestrictions: { country: "us" },
        fields: ["address_components", "geometry", "icon", "name"],
        //origin: center,
        strictBounds: false,
        types: ["address"],
    };
    const autocomplete = new google.maps.places.Autocomplete(input, options);

    autocomplete.addListener("place_changed", handleGeoAddressChange);

    function handleGeoAddressChange() {

        debugger;

        const place = autocomplete.getPlace();
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

        debugger;

        const $form = $('.js-form').find('form');
        let formData = new FormData($form.get(0));
        formData.delete('professional_edit_profile_form[_token]');
        formData.append('skip_validation', true);
        formData.append('changeableField', true);
        formData.set('geoAddress', geoAddress);
        let route = $('.js-form').attr('data-route');
        // todo remove regions from this form submit as we don't want to filter off of regions but just geo address
        // todo also take into consideration the radius as well. How do you want to account for that.
        // todo add select all logic to the schools using some select 2 function. Not sure yet how to do that.
        // todo add unselect all as well
        // todo consider adding map markers for the schools as well
        // todo add the pre_set event to the form as well

        debugger;
        $.ajax({
            url: route,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false
        }).then((data, textStatus, jqXHR) => {

            debugger;

        }).catch((jqXHR) => {

            debugger;
            const errorData = JSON.parse(jqXHR.responseText);

            $('.js-schools-container').replaceWith(
                // ... with the returned one from the AJAX response.
                $(errorData.formMarkup).find('.js-schools-container')
            );

            $('#professional_edit_profile_form_schools').select2({
                placeholder: "Select school(s)",
                allowClear: true,
                width: '100%',
                sortResults: data => data.sort((a, b) => a.text.localeCompare(b.text))
            });

            // todo remove. We don't want to show the schools in the dropdown but rather the schools selected
            initMarkers();
        });


        // todo we can either add them as hidden fields to the form view or send the form up and pass as additional data.
        // todo I would say we want this to be persisted to the backend so when they come back to their profile they can see
        // todo that they have this logic set

    }

    function initMarkers() {

        debugger;

        let schoolJson = $('.js-form').find('.js-school-json').attr('data-school-json');

        if (typeof schoolJson !== 'undefined' && schoolJson !== false) {

            debugger;

            schoolJson = JSON.parse(schoolJson);

            schoolJson.forEach(function (school, index) {
                debugger;

                let marker = new google.maps.Marker({
                    position: { lat: parseFloat(school.latitude), lng: parseFloat(school.longitude) },
                    map: map,
                });

                google.maps.event.addListener(marker, 'click', function() {
                    var infoWindow = new google.maps.InfoWindow();
                    infoWindow.setContent(school.name);
                    infoWindow.open(map, marker);
                });

            });
        }
    }



    //"administrative_area_level_1", "administrative_area_level_3", "locality", "postal_code"

     /*   countyJson.forEach(function (county, index) {

            debugger;
            let polygon = new google.maps.Polygon({
                paths: county.coordinates,
                //strokeColor: '#FF0000',
                //strokeOpacity: 0.8,
                strokeWeight: 0,
                fillColor: county.color,
                fillOpacity: 0.35
            });

            polygon.setMap(map);
        });*/

        debugger;
});