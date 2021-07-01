'use strict';

import $ from 'jquery';
import List from "list.js";
import Routing from "../Routing";

class RegionSelect {

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
        this.$wrapper.off('change', RegionSelect._selectors.region);
    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            region: '.js-region'
        }
    }

    bindEvents() {

        this.$wrapper.on(
            'change',
            RegionSelect._selectors.region,
            this.handleRegionChange.bind(this)
        );
    }

    handleRegionChange(e) {

        if(e.cancelable) {
            e.preventDefault();
        }

        const $form = this.$wrapper.find('form');
        let formData = new FormData($form.get(0));
        formData.delete('professional_edit_profile_form[_token]');
        formData.append('skip_validation', true);
        formData.append('changeableField', true);

        // todo remove address search and radius search here

        this._changeRegion(formData)
            .then((data) => {

                debugger;
            }).catch((errorData) => {

                debugger;

            $('.js-schools-container').replaceWith(
                // ... with the returned one from the AJAX response.
                $(errorData.formMarkup).find('.js-schools-container')
            );

            $('#professional_edit_profile_form_schools').select2({
                placeholder: "Select school(s)",
                allowClear: true,
                width: '100%'
            });

        });

    }

    _changeRegion(data) {
        debugger;
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

export default RegionSelect;