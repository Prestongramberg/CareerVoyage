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

        debugger;
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
        debugger;

        if(e.cancelable) {
            e.preventDefault();
        }

        const formData = {};

        formData[$(e.target).attr('name')] = $(e.target).val();
        formData[$(PrimaryIndustrySelect._selectors.primaryIndustry).attr('name')] = $(PrimaryIndustrySelect._selectors.primaryIndustry).val();
        formData['skip_validation'] = true;
        formData['primary_industry_change'] = true;

        debugger;
        this._changePrimaryIndustry(formData)
            .then((data) => {
                debugger;
            }).catch((errorData) => {

            debugger;
            $('.js-secondary-industry-container').replaceWith(
                // ... with the returned one from the AJAX response.
                $(errorData.formMarkup).find('.js-secondary-industry-container')
            );
        });

    }

    _changePrimaryIndustry(data) {
        return new Promise((resolve, reject) => {
            debugger;
            $.ajax({
                url: this.route,
                method: 'POST',
                data: data
            }).then((data, textStatus, jqXHR) => {
                debugger;
                resolve(data);
            }).catch((jqXHR) => {
                debugger;
                const errorData = JSON.parse(jqXHR.responseText);
                reject(errorData);
            });
        });
    }

    render() {}
}

export default PrimaryIndustrySelect;