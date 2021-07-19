'use strict';

//import 'bootstrap';

import RegionSelect from "./Components/RegionSelect";

require('../css/report_builder.scss');
import '../js/plugins/query-builder/query-builder';
import ReportBuilder from "./Components/ReportBuilder";
import interact from 'interactjs/dist/interact.min';
import moment from 'moment';

import $ from 'jquery';
//window.$ = $;
import 'jquery-ui-bundle';
import 'daterangepicker';

require('select2/dist/js/select2.min');

window.interact = interact;
window.moment = moment;

$(document).ready(function () {
    new ReportBuilder($('#js-report-builder'), window.globalEventDispatcher);

    $('#report_reportGroups').select2({
        placeholder: "Report Groups",
        allowClear: true,
        width: '100%',
        sortResults: data => data.sort((a, b) => a.text.localeCompare(b.text))
    });

    $('#report_reportShare_userRole').select2({
        placeholder: "User roles",
        allowClear: true,
        width: '100%',
        sortResults: data => data.sort((a, b) => a.text.localeCompare(b.text))
    });

    $('#report_reportShare_users').select2({
        placeholder: "Users",
        allowClear: true,
        width: '100%',
        ajax: {
            url: Routing.generate('users_select2_search'),
            data: function (params) {

                let userRole = $('#report_reportShare_userRoles').val();
                let regions = [];
                let schools = [];

                let $regions = $('#report_reportShare_regions').find(':selected');
                $regions.each(function () {
                    regions.push(this.value);
                });

                let $schools = $('#report_reportShare_schools').find(':selected');
                $schools.each(function () {
                    schools.push(this.value);
                });

                return {
                    search: params.term,
                    userRole: userRole,
                    regions: regions,
                    schools: schools,
                    page: params.page || 1
                };
            }
        }
    });

    $('#report_reportShare_regions').select2({
        placeholder: "Regions",
        allowClear: true,
        width: '100%',
        sortResults: data => data.sort((a, b) => a.text.localeCompare(b.text))
    });

    $('#report_reportShare_schools').select2({
        placeholder: "Schools",
        allowClear: true,
        width: '100%',
        sortResults: data => data.sort((a, b) => a.text.localeCompare(b.text))
    });

    new RegionSelect($('#js-report-builder'), window.globalEventDispatcher);

});