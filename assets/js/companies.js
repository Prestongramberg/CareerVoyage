import $ from 'jquery';
import CompanyForm from "./Components/CompanyForm";


$(document).ready(function() {
    debugger;
    new CompanyForm($('.js-company-form'), window.globalEventDispatcher);
});