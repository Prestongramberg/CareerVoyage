import $ from 'jquery';
import PrimaryIndustrySelect from "./Components/PrimaryIndustrySelect";

require('select2/dist/js/select2.min');

$(document).ready(function () {
    new PrimaryIndustrySelect($('.js-form'), window.globalEventDispatcher);

    $('.js-select2').select2({
        width: '100%'
    });


    debugger;

    let countyJson = $('.js-form').attr('data-county-json')

    if (typeof countyJson !== 'undefined' && countyJson !== false) {

        debugger;
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

            debugger;
            let coords = county.coordinates;

            debugger;
            for (var i = 0; i < coords.length; i++) {
                // array for the LatLng coordinates
                var polygonCoords = [];
                debugger;
                // go through each coordinate in the array.
                // GeoJSON is [longitude,latitude]
                for (var j = 0; j < coords[i].length; j++) {


                    debugger;

                    for (var k = 0; k < coords[i][j].length; k++) {

                        debugger;
                        var pt = new google.maps.LatLng(coords[i][j][k][1], coords[i][j][k][0])
                        polygonCoords.push(pt);
                        count++;
                    }



                    debugger;
                   /* var pt = new google.maps.LatLng(coords[i][j][1], coords[i][j][0])
                    polygonCoords.push(pt);
                    count++;*/
                }

                debugger;
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

            console.log("count: " + count);

        });
    }



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