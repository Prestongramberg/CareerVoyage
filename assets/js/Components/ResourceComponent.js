'use strict';

import $ from 'jquery';
import List from "list.js";
import Routing from "../Routing";
import UIkit from "uikit";

class ResourceComponent {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     */
    constructor($wrapper, globalEventDispatcher) {

        debugger;

        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;

        this.unbindEvents();
        this.bindEvents();
    }

    unbindEvents() {

        this.$wrapper.off('click', ResourceComponent._selectors.addResourceButton);
        $(document).off('click', ResourceComponent._selectors.addResourceFormSubmitButton);
        $(document).off('change', ResourceComponent._selectors.resourceTypeSelect);
        this.$wrapper.off('click', ResourceComponent._selectors.editResourceButton);
        this.$wrapper.off('click', ResourceComponent._selectors.deleteResourceButton);
        $(document).off('click', ResourceComponent._selectors.editResourceFormSubmitButton);
    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            addResourceButton: '.js-add-resource-button',
            addResourceFormSubmitButton: '.js-add-resource-form-submit-button',
            resourceTypeSelect: '.js-resource-type-select',
            resourceListContainer: '.js-resource-list-container',
            editResourceButton: '.js-edit-resource-button',
            deleteResourceButton: '.js-delete-resource-button',
            editResourceFormSubmitButton: '.js-edit-resource-form-submit-button'
        }
    }

    bindEvents() {

        this.$wrapper.on('click', ResourceComponent._selectors.addResourceButton, this.handleAddResourceButtonClick.bind(this));
        $(document).on('click', ResourceComponent._selectors.addResourceFormSubmitButton, this.handleAddResourceFormSubmit.bind(this));
        $(document).on('change', ResourceComponent._selectors.resourceTypeSelect, this.handleResourceTypeSelectChange.bind(this));
        this.$wrapper.on('click', ResourceComponent._selectors.editResourceButton, this.handleEditResourceButtonClick.bind(this));
        this.$wrapper.on('click', ResourceComponent._selectors.deleteResourceButton, this.handleDeleteResourceButtonClick.bind(this));
        $(document).on('click', ResourceComponent._selectors.editResourceFormSubmitButton, this.handleEditResourceFormSubmit.bind(this));
    }

    handleAddResourceButtonClick(event) {

        if (event.cancelable) {
            event.preventDefault();
        }

        let url = $(event.currentTarget).attr('data-url');

        $.ajax({
            url: url,
            method: 'GET'
        }).then((data, textStatus, jqXHR) => {

        }).catch((jqXHR) => {
            const errorData = JSON.parse(jqXHR.responseText);

            UIkit.modal('#js-resource-component-modal').show();

            $('#js-resource-component-modal').find('.uk-modal-body').html(errorData.formMarkup);
        });
    }


    handleResourceTypeSelectChange(e) {

        if (e.cancelable) {
            e.preventDefault();
        }

        const formData = {};
        let url = $(e.target).closest('form').attr('action');
        formData[$(e.target).attr('name')] = $(e.target).val();
        formData['skip_validation'] = true;

        this._changeField(formData, url)
            .then((data) => {
            }).catch((errorData) => {

            $('.js-resource-type-container').replaceWith(
                $(errorData.formMarkup).find('.js-resource-type-container')
            );
        });
    }

    _changeField(data, url) {

        return new Promise((resolve, reject) => {
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

    handleEditResourceButtonClick(event) {

        if (event.cancelable) {
            event.preventDefault();
        }

        let url = $(event.currentTarget).attr('data-url');

        $.ajax({
            url: url,
            method: 'GET'
        }).then((data, textStatus, jqXHR) => {

        }).catch((jqXHR) => {
            const errorData = JSON.parse(jqXHR.responseText);

            UIkit.modal('#js-resource-component-modal').show();

            $('#js-resource-component-modal').find('.uk-modal-body').html(errorData.formMarkup);
        });
    }

    handleDeleteResourceButtonClick(event) {

        if (event.cancelable) {
            event.preventDefault();
        }

        let url = $(event.currentTarget).attr('data-url');

        $.ajax({
            url: url,
            method: 'GET'
        }).then((data, textStatus, jqXHR) => {

            $(ResourceComponent._selectors.resourceListContainer).find('#resource-' + data.id).remove();

        }).catch((jqXHR) => {
            const errorData = JSON.parse(jqXHR.responseText);
        });
    }

    handleAddResourceFormSubmit(e) {

        if (e.cancelable) {
            e.preventDefault();
        }

        let $addResourceForm = $('.js-add-resource-form');
        let url = $addResourceForm.attr('action');
        let formData = new FormData($addResourceForm.get(0));

        return new Promise((resolve, reject) => {
            $.ajax({
                url: url,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false
            }).then((data, textStatus, jqXHR) => {

                const html = resourceTemplate(data.id, data.title, data.description, data.resourceUrl, data.editUrl, data.deleteUrl);
                $(ResourceComponent._selectors.resourceListContainer).append($($.parseHTML(html)));

                UIkit.modal('#js-resource-component-modal').hide();

                window.Pintex.notification("Resource successfully added", "success");

            }).catch((jqXHR) => {
                const errorData = JSON.parse(jqXHR.responseText);

                $('#js-resource-component-modal').find('.uk-modal-body').html(errorData.formMarkup);
            });
        });
    }

    handleEditResourceFormSubmit(e) {

        if (e.cancelable) {
            e.preventDefault();
        }

        let $editResourceForm = $('.js-edit-resource-form');
        let url = $editResourceForm.attr('action');
        let formData = new FormData($editResourceForm.get(0));

        return new Promise((resolve, reject) => {
            $.ajax({
                url: url,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false
            }).then((data, textStatus, jqXHR) => {

                const html = resourceTemplate(data.id, data.title, data.description, data.resourceUrl, data.editUrl, data.deleteUrl);

                $(ResourceComponent._selectors.resourceListContainer).find('#resource-' + data.id).replaceWith($($.parseHTML(html)));

                UIkit.modal('#js-resource-component-modal').hide();

                window.Pintex.notification("Resource successfully updated", "success");

            }).catch((jqXHR) => {
                const errorData = JSON.parse(jqXHR.responseText);

                $('#js-resource-component-modal').find('.uk-modal-body').html(errorData.formMarkup);
            });
        });
    }
}

const resourceTemplate = (id, title, description, resourceUrl, editUrl, deleteUrl) => `
    <div id="resource-${id}" class="uk-grid uk-flex-middle" uk-grid>
        <div class="uk-width-expand">
            <dt>${title}</dt>
            <dd>${description}</dd>
        </div>
        <div class="uk-width-auto">
             <a href="${resourceUrl}"
                   class="uk-button uk-button-default uk-button-small view"
                   target="_blank">View</a>
        </div>
        <div class="uk-width-auto">
            <a href="#" class="uk-button uk-button-default uk-button-small js-edit-resource-button" data-url="${editUrl}">Edit</a>
        </div>
        <button class="js-delete-resource-button" type="button" data-url="${deleteUrl}" uk-close></button>
    </div>

`;

export default ResourceComponent;