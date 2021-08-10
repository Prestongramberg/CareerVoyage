import $ from 'jquery';
require('select2/dist/js/select2.min');

$(document).ready(function () {

    console.log("manage requests page");

    $('.js-manage-requests-container').on('click', '.js-request-item', function(event) {

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

            console.log("success");


            UIkit.modal('#js-manage-request-modal').show();

            debugger;
            $('#js-manage-request-modal').find('.uk-modal-body').html(data.formMarkup);


           /* $('.js-secondary-industry-container').replaceWith(
                // ... with the returned one from the AJAX response.
                $(errorData.formMarkup).find('.js-secondary-industry-container')
            );*/


        }).catch((jqXHR) => {
            const errorData = JSON.parse(jqXHR.responseText);
            console.log("error");
        });

    });



});