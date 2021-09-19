'use strict';

import $ from 'jquery';
import List from "list.js";
import Routing from "../Routing";

class RadiusSelect {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     * @param initMarkers
     */
    constructor($wrapper, globalEventDispatcher, initMarkers) {

        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.route = this.$wrapper.attr('data-route');
        this.initMarkers = initMarkers;

        this.unbindEvents();
        this.bindEvents();
        this.render();
    }

    unbindEvents() {
        this.$wrapper.off('change', RadiusSelect._selectors.radius);
    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            radius: '.js-radius'
        }
    }

    bindEvents() {

        this.$wrapper.on(
            'change',
            RadiusSelect._selectors.radius,
            this.handleRadiusChange.bind(this)
        );
    }

    handleRadiusChange(e) {

        if(e.cancelable) {
            e.preventDefault();
        }

        const $form = this.$wrapper.find('form');
        let formData = new FormData($form.get(0));
        formData.delete('professional_edit_profile_form[_token]');
        formData.append('skip_validation', true);
        formData.append('changeableField', true);

        this._changeRadius(formData)
            .then((data) => {
            }).catch((errorData) => {

            $('.js-schools-container').replaceWith(
                // ... with the returned one from the AJAX response.
                $(errorData.formMarkup).find('.js-schools-container')
            );

            $('#professional_edit_profile_form_schools').select2({
                placeholder: "Volunteer schools",
                allowClear: true,
                width: '100%'
            });

            $('#company_form_schools').select2({
                placeholder: "Volunteer schools",
                allowClear: true,
                width: '100%'
            });

            this.initMarkers();

        });

    }

    _changeRadius(data) {
        return new Promise((resolve, reject) => {
            $.ajax({
                url: this.route,
                method: 'POST',
                data: data,
                processData: false,
                contentType: false
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

export default RadiusSelect;