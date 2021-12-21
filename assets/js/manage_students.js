import $ from "jquery";
require('select2/dist/js/select2.min');
import ManageUsersComponent from './Components/ManageUsersComponent';

$(document).ready(function () {

    new ManageUsersComponent(
        $('.js-manage-users-container')
    );

});