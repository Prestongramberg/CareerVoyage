import $ from 'jquery';
import PrimaryIndustrySelect from "./Components/PrimaryIndustrySelect";

$(document).ready(function() {
    debugger;
    new PrimaryIndustrySelect($('.js-form'), window.globalEventDispatcher);
});