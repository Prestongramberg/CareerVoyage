'use strict';

import $ from 'jquery';
import Routing from '../Routing';
import flatpickr from "flatpickr";

class RadioChoiceField {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     */
    constructor($wrapper, globalEventDispatcher) {
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.url = this.$wrapper.attr('data-url');

        this.unbindEvents()
            .bindEvents();
    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            changeableField: 'input[type="radio"]'
        }
    }

    bindEvents() {
        this.$wrapper.on('click', RadioChoiceField._selectors.changeableField, this.handleFieldChange.bind(this));
        return this;
    }

    unbindEvents() {
        this.$wrapper.off('click', RadioChoiceField._selectors.changeableField);
        return this;
    }

    handleFieldChange(e) {

        debugger;
        const formData = {};
        let fieldName = this.$wrapper.attr('data-field-name');
        formData[$(e.target).attr('name')] = $(e.target).val();
        formData['changeableField'] = $(e.target).attr('name');
        formData['skip_validation'] = true;
        this._changeField(formData)
            .then((data) => {
            }).catch((errorData) => {
                debugger;
            $(`[data-field-name="${fieldName}"]`).html(
                $(errorData.formMarkup).find(`[data-field-name="${fieldName}"]`).html()
            );

            if(document.getElementById("experience_startDate")) {
                $("#experience_startDate").flatpickr({
                    dateFormat: "m/d/Y",
                    minDate: "today"
                });
            }

            if(document.getElementById("experience_endDate")) {
                $("#experience_endDate").flatpickr({
                    dateFormat: "m/d/Y",
                    minDate: "today"
                });
            }

        });
    }

    _changeField(data) {
        return new Promise((resolve, reject) => {
            const url = this.url
            $.ajax({
                url: url,
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
}

export default RadioChoiceField;