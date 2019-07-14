import $ from 'jquery';
import CompanyResultsPage from "./Components/CompanyResultsPage";

$(document).ready(function() {
    new CompanyResultsPage($('#app'), window.globalEventDispatcher);
});