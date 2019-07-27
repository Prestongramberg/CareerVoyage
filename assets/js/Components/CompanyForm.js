'use strict';

import $ from 'jquery';
import List from "list.js";
import Routing from "../Routing";

class CompanyForm {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     * @param editForm
     */
    constructor($wrapper, globalEventDispatcher, editForm = false) {

        debugger;
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.editForm = editForm;
        this.companyId = this.$wrapper.attr('data-company');

        this.unbindEvents();
        this.bindEvents();
        this.render();
    }

    unbindEvents() {
        this.$wrapper.off('change', CompanyForm._selectors.primaryIndustry);
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
            CompanyForm._selectors.primaryIndustry,
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
        formData[$(CompanyForm._selectors.primaryIndustry).attr('name')] = $(CompanyForm._selectors.primaryIndustry).val();
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
            const url = this.editForm ? Routing.generate('company_edit', {id: this.companyId}) : Routing.generate('company_new');

            $.ajax({
                url,
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

export default CompanyForm;