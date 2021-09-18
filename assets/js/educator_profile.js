import $ from 'jquery';
import PrimaryIndustrySelect from "./Components/PrimaryIndustrySelect";
import RegionSelect from "./Components/RegionSelect";
import RadiusSelect from "./Components/RadiusSelect";
import SchoolSelect from "./Components/SchoolSelect";

require('select2/dist/js/select2.min');
import Inputmask from "inputmask";
import VideoComponent from "./Components/VideoComponent";

$(document).ready(function () {


    let primaryIndustrySelector = '#educator_edit_profile_form_primaryIndustries';
    let secondaryIndustrySelector = '#educator_edit_profile_form_secondaryIndustries';


    $(primaryIndustrySelector).select2({
        placeholder: "Select Industry",
        allowClear: true,
        width: '100%',
        sortResults: data => data.sort((a, b) => a.text.localeCompare(b.text))
    });

    $(secondaryIndustrySelector).select2({
        placeholder: "Select Profession",
        allowClear: true,
        width: '100%',
        sortResults: data => data.sort((a, b) => a.text.localeCompare(b.text))
    });

    $('#educator_edit_profile_form_myCourses').select2({
        placeholder: "Select Courses, Clubs, Positions",
        allowClear: true,
        width: '100%',
        sortResults: data => data.sort((a, b) => a.text.localeCompare(b.text))
    });

    $('#educator_edit_profile_form_studentUsers').select2({
        placeholder: "Select Students",
        allowClear: true,
        width: '100%',
        sortResults: data => data.sort((a, b) => a.text.localeCompare(b.text))
    });

    $(document).on('click', '.js-select-all-students', function(e) {
        $("#educator_edit_profile_form_studentUsers > option").prop("selected", "selected");// Select All Options
        $("#educator_edit_profile_form_studentUsers").trigger("change");// Trigger change to select 2
    });

    $(document).on('click', '.js-select-all-courses', function(e) {
        $("#educator_edit_profile_form_myCourses > option").prop("selected", "selected");// Select All Options
        $("#educator_edit_profile_form_myCourses").trigger("change");// Trigger change to select 2
    });

    $(document).on('click', '.js-select-all-secondary-industries', function(e) {

        $("#educator_edit_profile_form_secondaryIndustries > optgroup > option").prop("selected", "selected");// Select All Options
        $("#educator_edit_profile_form_secondaryIndustries").trigger("change");// Trigger change to select 2
    });

    $(document).on('click', '.js-select-all-primary-industries', function(e) {
        $("#educator_edit_profile_form_primaryIndustries > option").prop("selected", "selected");// Select All Options
        $("#educator_edit_profile_form_primaryIndustries").trigger("change");// Trigger change to select 2
    });

    if (document.getElementById("educator_edit_profile_form_username")) {

        document.getElementById("educator_edit_profile_form_username").onfocus = function () {
            this.removeAttribute('readonly');
            this.setAttribute('autocomplete', 'chrome-off');
        }
    }

    if (document.getElementById("educator_edit_profile_form_email")) {

        document.getElementById("educator_edit_profile_form_email").onfocus = function () {
            this.removeAttribute('readonly');
            this.setAttribute('autocomplete', 'chrome-off');
        }
    }

    if (document.getElementById("educator_edit_profile_form_plainPassword")) {

        document.getElementById("educator_edit_profile_form_plainPassword").onfocus = function () {
            this.removeAttribute('readonly');
            this.setAttribute('autocomplete', 'chrome-off');
        }
    }


    if (document.getElementById("validation_groups")) {
        UIkit.util.on('.uk-switcher', 'show', function (ev) {

            if ($(ev.target).hasClass('account_details_personal')) {
                $('#tab').val('#account-details-profile');
                $('#validation_groups').val('EDUCATOR_PROFILE_PERSONAL');
                location.hash = 'account-details-profile';
            }

            if ($(ev.target).hasClass('account_details_students')) {
                $('#validation_groups').val('EDUCATOR_PROFILE_STUDENT');
                $('#tab').val('#account-details-profile-students');
                location.hash = 'account-details-profile-students';
            }

            if ($(ev.target).hasClass('account_details_videos')) {
                $('#validation_groups').val('EDUCATOR_PROFILE_VIDEO');
                $('#tab').val('#account-details-profile-videos');
                location.hash = 'account-details-profile-videos';
            }

            if ($(ev.target).hasClass('account_details_account')) {
                $('#validation_groups').val('EDUCATOR_PROFILE_ACCOUNT');
                $('#tab').val('#account-details-profile-account');
                location.hash = 'account-details-profile-account';
            }
        });
    }

    if (document.getElementById('educator_edit_profile_form_phone')) {
        var selector = document.getElementById("educator_edit_profile_form_phone");
        var im = new Inputmask("(999) 999-9999");
        im.mask(selector);
    }

    new PrimaryIndustrySelect($('.js-form'), window.globalEventDispatcher, primaryIndustrySelector, secondaryIndustrySelector, false);
    new VideoComponent($('.js-video-component'), window.globalEventDispatcher);
});