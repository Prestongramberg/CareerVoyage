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
        this.selectCache = null;

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

    handlePrimaryIndustryChange(event) {

        if (event.cancelable) {
            event.preventDefault();
        }

        debugger;
        let $form = $(this.primaryIndustrySelector).closest('form');
        let formName = $form.attr('name');
        let tokenName = formName + "[_token]";
        var formData = new FormData($form.get(0));

        formData.delete(tokenName);
        formData.append('primary_industry_change', "1");
        formData.append('changeableField', "1");
        formData.append('skip_validation', "1");

        if (!this.selectCache) {
            setTimeout(() => {

                this._changePrimaryIndustry(formData)
                    .then((data) => {
                    }).catch((errorData) => {

                        debugger;

                        console.log(errorData);
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
                        sortResults: data => data.sort((a, b) => a.text.localeCompare(b.text)),
                        "language": {
                            "noResults": function(){
                                return "Please choose a career sector / industry first";
                            }
                        }
                    });

                    $('#company_form_secondaryIndustries').select2({
                        placeholder: "Select Professions",
                        allowClear: true,
                        width: '100%',
                        "language": {
                            "noResults": function(){
                                return "Please choose a career sector / industry first";
                            }
                        },
                    });

                    $('#professional_registration_form_secondaryIndustries').select2({
                        placeholder: "Select Professions",
                        allowClear: true,
                        width: '100%',
                        "language": {
                            "noResults": function(){
                                return "Please choose a career sector / industry first";
                            }
                        },
                    });


                });

                this.selectCache = null;
            }, 100)
        }


        this.selectCache = event;
    }

    _changePrimaryIndustry(data) {
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

    render() {
    }
}

export default PrimaryIndustrySelect;