import $ from "jquery";

require('select2/dist/js/select2.min');
const moment = require('moment-timezone');
require('./vendor/jquery-datetimepicker.js');
require('select2/dist/js/select2.min');

$(document).ready(function () {

    $('.js-select-all-students').on('change', function (event) {

        let checked = $(event.target).is(':checked'); // Checkbox state

        if (checked) {
            $('[name="studentUser[]"]').prop("checked", true);
            setSelectedStudents();
        } else {
            $('[name="studentUser[]"]').prop("checked", false);
            setSelectedStudents();
        }
    });

    $('[name="studentUser[]"]').on('change', function(event) {
        setSelectedStudents();
    });

    function setSelectedStudents() {
        let numberOfSelectedUsers = $('[name="studentUser[]"]:checked').length;

        if(numberOfSelectedUsers > 0) {
            $('.js-total-users-selected').html(`(${numberOfSelectedUsers} selected)`);
        } else {
            $('.js-total-users-selected').html('');
        }
    }

    $('.js-manage-students-container').on('click', '.js-bulk-action-apply-button', function (event) {

        debugger;

        if (event.cancelable) {
            event.preventDefault();
        }

        $('.js-bulk-action-error').hide();
        $('.js-select-student-error').hide();
        $('.js-bulk-action-dropdown').removeClass('uk-form-danger');

        let bulkAction = $('.js-bulk-action-dropdown').val();
        let students = $('input[name="studentUser[]"]:checked')
        let studentCount = students.length;

        if (studentCount === 0) {
            $('.js-select-student-error').show();
            $('.js-bulk-action-dropdown').addClass('uk-form-danger');
            return;
        }

        if (!bulkAction) {
            $('.js-bulk-action-error').show();
            $('.js-bulk-action-dropdown').addClass('uk-form-danger');
            return;
        }

        debugger;

        let data = {studentCount: studentCount};

        if(studentCount === 1) {
            data.studentId = students[0].value;
        }

        $.ajax({
            url: bulkAction,
            method: 'GET',
            data: data
        }).then((data, textStatus, jqXHR) => {

            debugger;
            UIkit.modal('#js-bulk-action-modal').show();

            $('#js-bulk-action-modal').html(data.formMarkup);

            if ($('#supervising_teacher_form_supervisingTeachers').length) {
                $('#supervising_teacher_form_supervisingTeachers').select2({
                    placeholder: "Supervising Teacher",
                    allowClear: true,
                    width: '100%'
                });
            }

        }).catch((jqXHR) => {
            debugger;
            const errorData = JSON.parse(jqXHR.responseText);
        });

    });

    $('#js-bulk-action-modal').on('submit', '#js-bulk-action-form', function (event) {

        debugger;

        if (event.cancelable) {
            event.preventDefault();
        }

        let form = $(event.target).get(0);
        let url = $(event.target).attr('action');

        debugger;

        var formData = new FormData(form);

        let students = $('input[name="studentUser[]"]:checked')
        students.each((i, obj) => {
            formData.append('studentIds[]', obj.value);
        });

        debugger;

        $.ajax({
            url: url,
            data: formData,
            contentType: false,
            processData: false,
            type: "POST"
        }).then((data, textStatus, jqXHR) => {

            window.location.href = data.redirectUrl;
            debugger;
        }).catch((jqXHR) => {

            const errorData = JSON.parse(jqXHR.responseText);
            $('#js-bulk-action-modal').html(errorData.formMarkup);

            if ($('#supervising_teacher_form_supervisingTeachers').length) {
                $('#supervising_teacher_form_supervisingTeachers').select2({
                    placeholder: "Supervising Teacher",
                    allowClear: true,
                    width: '100%'
                });
            }
        });

        debugger;

    });

});