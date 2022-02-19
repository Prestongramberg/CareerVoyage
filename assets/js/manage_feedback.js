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
import RadioChoiceField from "./Components/RadioChoiceField";

$(document).ready(function () {
    console.log("manage feedback page");

    $("#startDateAndTime_left_date").flatpickr({
        dateFormat: "m/d/Y"
    });
    $("#startDateAndTime_right_date").flatpickr({
        dateFormat: "m/d/Y"
    });

});