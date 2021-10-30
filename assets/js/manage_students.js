import $ from "jquery";

require('select2/dist/js/select2.min');
const moment = require('moment-timezone');
require('./vendor/jquery-datetimepicker.js');

$(document).ready(function () {

    $('.js-select-all-students').on('change', function(event) {

        let checked = $(event.target).is(':checked'); // Checkbox state

        if(checked) {
            $('[name="studentUser[]"]').prop("checked", true);
        } else {
            $('[name="studentUser[]"]').prop("checked", false);
        }
    });
});