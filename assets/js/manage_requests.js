import $ from "jquery";

require('select2/dist/js/select2.min');
const moment = require('moment-timezone');
require('./vendor/jquery-datetimepicker.js');

$(document).ready(function () {

    const urlParams = new URLSearchParams(location.search);
    for (const [key, value] of urlParams) {

        if(key === 'id') {
            let url = $(`#nc_${value}`).attr('data-action-url');


            $.ajax({
                url: url,
                method: 'GET'
            }).then((data, textStatus, jqXHR) => {

                UIkit.modal('#js-manage-request-modal').show();

                $('#js-manage-request-modal').html(data.formMarkup);

            }).catch((jqXHR) => {
                const errorData = JSON.parse(jqXHR.responseText);
            });
        }
    }

    $('.js-manage-requests-container').on('click', '.js-request-item', function (event) {

        if (event.cancelable) {
            event.preventDefault();
        }

        debugger;
        let url = $(event.currentTarget).attr('data-action-url');

        debugger;

        $.ajax({
            url: url,
            method: 'GET'
        }).then((data, textStatus, jqXHR) => {

            UIkit.modal('#js-manage-request-modal').show();

            $('#js-manage-request-modal').html(data.formMarkup);

        }).catch((jqXHR) => {
            const errorData = JSON.parse(jqXHR.responseText);
        });

    });

    $('#js-manage-request-modal').on('click', '.js-request-item', function (event) {

        if (event.cancelable) {
            event.preventDefault();
        }

        debugger;
        let url = $(event.currentTarget).attr('data-action-url');

        debugger;

        $.ajax({
            url: url,
            method: 'GET'
        }).then((data, textStatus, jqXHR) => {

            UIkit.modal('#js-manage-request-modal').show();

            $('#js-manage-request-modal').html(data.formMarkup);

        }).catch((jqXHR) => {
            const errorData = JSON.parse(jqXHR.responseText);
        });

    });

    $('#js-manage-request-modal').on('change', '.js-actions', function (event) {

        if (event.cancelable) {
            event.preventDefault();
        }

        let url = $(event.target).val();

        debugger;

        $.ajax({
            url: url,
            method: 'GET'
        }).then((data, textStatus, jqXHR) => {

            //UIkit.modal('#js-manage-request-modal').show();

            $('#js-manage-request-modal').html(data.formMarkup);

            if($('.live-chat__window-thread').length) {
                $('.live-chat__window-thread').scrollTop($('.live-chat__window-thread').get(0).scrollHeight);
            }

            if($('.js-send-message-textarea').length) {
                $('.js-send-message-textarea').focus();
            }

            /**
             * Time Pickers
             */
            $('.uk-timepicker').each(function (index) {
                var $elem = $(this);
                var dropDirection = $elem.hasClass('uk-timepicker-up') ? "up" : "down";

                $elem.daterangepicker({
                    drops: dropDirection,
                    singleDatePicker: true,
                    timePicker: true,
                    timePickerIncrement: 5,
                    linkedCalendars: false,
                    showCustomRangeLabel: false,
                    locale: {
                        format: 'MM/DD/YYYY h:mm A'
                    }
                }, function (start, end, label) {
                    console.log('New date range selected: ' + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD') + ' (predefined range: ' + label + ')');
                });
            });


        }).catch((jqXHR) => {
            const errorData = JSON.parse(jqXHR.responseText);
        });

    });


    $('#js-manage-request-modal').on('click', '.js-request-management-submit', function (event) {
        let name = $(event.target).attr('name');
        $('#js-manage-request-form').append(`<input type="hidden" name="${name}">`);
    });


    $('#js-manage-request-modal').on('submit', '#js-manage-request-form', function (event) {

        debugger;

        if (event.cancelable) {
            event.preventDefault();
        }

        let form = $(event.target).get(0);
        let url = $(event.target).attr('action');

        debugger;

        var formData = new FormData(form);

        debugger;

        $.ajax({
            url: url,
            data: formData,
            contentType: false,
            processData: false,
            type: "POST"
        }).then((data, textStatus, jqXHR) => {

            $('#js-manage-request-modal').html(data.formMarkup);

            if($('.live-chat__window-thread').length) {
                $('.live-chat__window-thread').scrollTop($('.live-chat__window-thread').get(0).scrollHeight);
            }

            if($('.js-send-message-textarea').length) {
                $('.js-send-message-textarea').focus();
            }

        }).catch((jqXHR) => {
            const errorData = JSON.parse(jqXHR.responseText);
        });

        debugger;

    });

});