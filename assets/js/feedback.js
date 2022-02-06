import $ from 'jquery';
import PrimaryIndustrySelect from "./Components/PrimaryIndustrySelect";
import RegionSelect from "./Components/RegionSelect";
import RadiusSelect from "./Components/RadiusSelect";
import SchoolSelect from "./Components/SchoolSelect";

require('select2/dist/js/select2.min');
import Inputmask from "inputmask";
import VideoComponent from "./Components/VideoComponent";
import VCountdown from './Components/VCountdown.js';
import flatpickr from "flatpickr";
import Tagify from '@yaireo/tagify'
import Routing from "./Routing";
import ResourceComponent from "./Components/ResourceComponent";
import RadioChoiceField from "./Components/RadioChoiceField";

$(document).ready(function () {

    $(document).on('change', '.js-school', function (event) {

        let url = $(event.target).attr('data-route');

        if (event.cancelable) {
            event.preventDefault();
        }

        let $form = $('[name="schoolInfo"]').closest('form');
        let formName = $form.attr('name');
        let tokenName = formName + "[_token]";
        var formData = new FormData($form.get(0));

        formData.delete(tokenName);
        formData.append('changeableField', "1");
        formData.append('skip_validation', "1");

        $.ajax({
            url: url,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false
        }).then((data, textStatus, jqXHR) => {
            resolve(data);
        }).catch((jqXHR) => {
            debugger;
            const errorData = JSON.parse(jqXHR.responseText);

            $('.js-school-other-container').replaceWith(
                $(errorData.formMarkup).find('.js-school-other-container')
            );

        });

    });


    $(document).on('change', '.js-company', function (event) {

        let url = $(event.target).attr('data-route');

        if (event.cancelable) {
            event.preventDefault();
        }

        let $form = $('[name="companyInfo"]').closest('form');
        let formName = $form.attr('name');
        let tokenName = formName + "[_token]";
        var formData = new FormData($form.get(0));

        formData.delete(tokenName);
        formData.append('changeableField', "1");
        formData.append('skip_validation', "1");

        $.ajax({
            url: url,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false
        }).then((data, textStatus, jqXHR) => {
            resolve(data);
        }).catch((jqXHR) => {
            debugger;
            const errorData = JSON.parse(jqXHR.responseText);

            $('.js-company-other-container').replaceWith(
                $(errorData.formMarkup).find('.js-company-other-container')
            );

        });

    });

});