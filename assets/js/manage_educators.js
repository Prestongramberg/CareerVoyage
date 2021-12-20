import $ from "jquery";
require('select2/dist/js/select2.min');
import ManageUsersComponent from './Components/ManageUsersComponent';

$(document).ready(function () {

    let modalRenderSuccessHandler = (data, textStatus, jqXHR) => {

        UIkit.modal('#js-bulk-action-modal').show();

        $('#js-bulk-action-modal').html(data.formMarkup);

        if ($('#supervising_teacher_form_supervisingTeachers').length) {
            $('#supervising_teacher_form_supervisingTeachers').select2({
                placeholder: "Supervising Teacher",
                allowClear: true,
                width: '100%'
            });
        }
    };

    let formSubmitSuccessHandler = (data, textStatus, jqXHR) => {
        window.location.href = data.redirectUrl;
    };

    let formSubmitErrorHandler = (jqXHR) => {
        const errorData = JSON.parse(jqXHR.responseText);
        $('#js-bulk-action-modal').html(errorData.formMarkup);

        if ($('#supervising_teacher_form_supervisingTeachers').length) {
            $('#supervising_teacher_form_supervisingTeachers').select2({
                placeholder: "Supervising Teacher",
                allowClear: true,
                width: '100%'
            });
        }
    };

    new ManageUsersComponent(
        $('.js-manage-users-container'),
        modalRenderSuccessHandler,
        formSubmitSuccessHandler,
        formSubmitErrorHandler
    );
});