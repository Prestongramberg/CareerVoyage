import $ from 'jquery';
import PrimaryIndustrySelect from "./Components/PrimaryIndustrySelect";
import RegionSelect from "./Components/RegionSelect";
import RadiusSelect from "./Components/RadiusSelect";
import SchoolSelect from "./Components/SchoolSelect";

require('select2/dist/js/select2.min');
import Inputmask from "inputmask";
import VideoComponent from "./Components/VideoComponent";

$(document).ready(function () {


    $('#professional_registration_form_secondaryIndustries').select2({
        placeholder: "Select Professions",
        allowClear: true,
        width: '100%',
        "language": {
            "noResults": function(){
                return "Please choose a career sector / industry first";
            }
        },
    });

    /*
        $('.js-select2').select2({
            width: '100%'
        });
    */


/*    $(document).on('click', '.js-select-all-schools', function(e) {
        $("#professional_edit_profile_form_schools > option").prop("selected", "selected");// Select All Options
        $("#professional_edit_profile_form_schools").trigger("change");// Trigger change to select 2
    });

    $('#professional_edit_profile_form_schools').select2({
        placeholder: "Volunteer schools",
        allowClear: true,
        width: '100%',
        sortResults: data => data.sort((a, b) => a.text.localeCompare(b.text))
    });

    $('#professional_edit_profile_form_regions').select2({
        placeholder: "Filter by region",
        allowClear: true,
        width: '100%'
    });

    $('#professional_edit_profile_form_secondaryIndustries').select2({
        placeholder: "Select Professions",
        allowClear: true,
        width: '100%',
        "language": {
            "noResults": function(){
                return "Please choose a career sector / industry first";
            }
        },
    });*/

    new PrimaryIndustrySelect($('.js-form'), window.globalEventDispatcher);
});