'use strict';

import $ from 'jquery';
import List from "list.js";
import Routing from "../Routing";

class PrimaryIndustrySelect {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     */
    constructor($wrapper, globalEventDispatcher) {

        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.route = this.$wrapper.attr('data-route');

        this.unbindEvents();
        this.bindEvents();
        this.render();
    }

    unbindEvents() {
        this.$wrapper.off('change', PrimaryIndustrySelect._selectors.primaryIndustry);
    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            primaryIndustry: '.js-primary-industry'
        }
    }

    bindEvents() {

        this.$wrapper.on(
            'change',
            PrimaryIndustrySelect._selectors.primaryIndustry,
            this.handlePrimaryIndustryChange.bind(this)
        );
    }

    handlePrimaryIndustryChange(e) {

        if(e.cancelable) {
            e.preventDefault();
        }

        const formData = {};

        formData[$(e.target).attr('name')] = $(e.target).val();
        formData[$(PrimaryIndustrySelect._selectors.primaryIndustry).attr('name')] = $(PrimaryIndustrySelect._selectors.primaryIndustry).val();
        formData['skip_validation'] = true;
        formData['primary_industry_change'] = true;

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

    render() {}
}

export default PrimaryIndustrySelect;