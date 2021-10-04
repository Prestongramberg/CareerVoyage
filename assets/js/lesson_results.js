import $ from 'jquery';

require('select2/dist/js/select2.min');

$(document).ready(function () {

    $('.js-primary-courses-filter').select2({
        width: '100%',
        placeholder: "Search by School Course",
        allowClear: true
    });

    $('.js-primary-industries-filter').select2({
        width: '100%',
        placeholder: "Search by Industry Sector",
        allowClear: true
    });
});