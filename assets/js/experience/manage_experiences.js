import $ from "jquery";
require('select2/dist/js/select2.min');
import ManageUsersComponent from '../Components/ManageUsersComponent';
import flatpickr from "flatpickr";

$(document).ready(function () {

/*
    new ManageUsersComponent(
        $('.js-manage-users-container')
    );
*/

    $("#item_filter_startDateAndTime_left_date").flatpickr({
        dateFormat: "m/d/Y"
    });
    $("#item_filter_startDateAndTime_right_date").flatpickr({
        dateFormat: "m/d/Y"
    });

    console.log('manage experiences');

});