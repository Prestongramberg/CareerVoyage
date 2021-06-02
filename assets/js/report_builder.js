'use strict';

//import 'bootstrap';

require('../css/report_builder.scss');
import '../js/plugins/query-builder/query-builder';
import ReportBuilder from "./Components/ReportBuilder";
import interact from 'interactjs/dist/interact.min';
import moment from 'moment';

import $ from 'jquery';
//window.$ = $;
import 'jquery-ui-bundle';
import 'daterangepicker';

window.interact = interact;
window.moment = moment;

$(document).ready(function () {
    new ReportBuilder($('#js-report-builder'), window.globalEventDispatcher);
});