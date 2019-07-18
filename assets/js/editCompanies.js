import $ from 'jquery';
import CompanyEditPage from "./Components/CompanyEditPage";

$(document).ready(function() {
    new CompanyEditPage($('#app'), window.globalEventDispatcher);
});