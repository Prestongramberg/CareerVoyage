'use strict';

import $ from 'jquery';
import List from "list.js";
import Routing from "../Routing";

class PrimaryIndustrySelect {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     * @param primaryIndustrySelector
     * @param secondaryIndustrySelector
     * @param clearSecondaryIndustries
     */
    constructor($wrapper, globalEventDispatcher, primaryIndustrySelector = '.js-primary-industry', secondaryIndustrySelector = null, clearSecondaryIndustries = true) {

        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.route = this.$wrapper.attr('data-route');
        this.primaryIndustrySelector = primaryIndustrySelector;
        this.secondaryIndustrySelector = secondaryIndustrySelector;
        this.clearSecondaryIndustries = clearSecondaryIndustries;

        this.unbindEvents();
        this.bindEvents();
        this.render();
    }

    unbindEvents() {
        this.$wrapper.off('change', this.primaryIndustrySelector);
    }

    bindEvents() {

        this.$wrapper.on('change', this.primaryIndustrySelector, this.handlePrimaryIndustryChange.bind(this));
    }

    handlePrimaryIndustryChange(e) {

        if (e.cancelable) {
            e.preventDefault();
        }

        debugger;
        const formData = {};

        if(!this.clearSecondaryIndustries) {
            formData[$(this.secondaryIndustrySelector).attr('name')] = $(this.secondaryIndustrySelector).val();
        }

        formData[$(e.target).attr('name')] = $(e.target).val();

        //formData[$(PrimaryIndustrySelect._selectors.primaryIndustry).attr('name')] = $(PrimaryIndustrySelect._selectors.primaryIndustry).val();

        formData['skip_validation'] = true;
        formData['primary_industry_change'] = true;

        debugger;

        this._changePrimaryIndustry(formData)
            .then((data) => {
            }).catch((errorData) => {
            $('.js-secondary-industry-container').replaceWith(
                // ... with the returned one from the AJAX response.
                $(errorData.formMarkup).find('.js-secondary-industry-container')
            );

            $('.js-select2').select2({
                width: '100%'
            });

            $('#educator_edit_profile_form_secondaryIndustries').select2({
                placeholder: "Select Profession",
                allowClear: true,
                width: '100%',
                sortResults: data => data.sort((a, b) => a.text.localeCompare(b.text))
            });

            $('#professional_edit_profile_form_secondaryIndustries').select2({
                placeholder: "Select Profession",
                allowClear: true,
                width: '100%',
                sortResults: data => data.sort((a, b) => a.text.localeCompare(b.text))
            });


        });

    }

    _changePrimaryIndustry(data) {
        return new Promise((resolve, reject) => {
            $.ajax({
                url: this.route,
                method: 'POST',
                data: data
            }).then((data, textStatus, jqXHR) => {
                resolve(data);
            }).catch((jqXHR) => {
                const errorData = JSON.parse(jqXHR.responseText);
                reject(errorData);
            });
        });
    }

    render() {
    }
}

export default PrimaryIndustrySelect;