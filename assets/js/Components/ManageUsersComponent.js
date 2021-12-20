'use strict';

import $ from 'jquery';
import List from "list.js";
import Routing from "../Routing";

class ManageUsersComponent {

    /**
     * @param $wrapper
     * @param modalRenderSuccessHandler
     * @param formSubmitSuccessHandler
     * @param formSubmitErrorHandler
     */
    constructor($wrapper, modalRenderSuccessHandler, formSubmitSuccessHandler, formSubmitErrorHandler) {
        this.$wrapper = $wrapper;
        this.modalRenderSuccessHandler = modalRenderSuccessHandler;
        this.formSubmitSuccessHandler = formSubmitSuccessHandler;
        this.formSubmitErrorHandler = formSubmitErrorHandler;

        this.unbindEvents();
        this.bindEvents();
    }

    unbindEvents() {

        this.$wrapper.off('change', ManageUsersComponent._selectors.primaryIndustryFilter);
        this.$wrapper.off('click', ManageUsersComponent._selectors.bulkActionApplyButton);
        $('#js-bulk-action-modal').off('submit', '#js-bulk-action-form');
    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            selectAllUsersCheckbox: '.js-select-all-users',
            bulkActionApplyButton: '.js-bulk-action-apply-button',
        }
    }

    bindEvents() {
        this.$wrapper.on('change', ManageUsersComponent._selectors.selectAllUsersCheckbox, this.handleSelectAllUsersCheckboxChange.bind(this));
        this.$wrapper.on('click', ManageUsersComponent._selectors.bulkActionApplyButton, this.handleBulkActionApplyButtonClick.bind(this));
        $('#js-bulk-action-modal').on('submit', '#js-bulk-action-form', this.handleBulkActionFormSubmit);
    }

    handleSelectAllUsersCheckboxChange(event) {

        let checked = $(event.target).is(':checked'); // Checkbox state

        if (checked) {
            $('[name="user[]"]').prop("checked", true);
            this._setSelectedUsers();
        } else {
            $('[name="user[]"]').prop("checked", false);
            this._setSelectedUsers();
        }
    }

    handleBulkActionApplyButtonClick(event) {

        if (event.cancelable) {
            event.preventDefault();
        }

        $('.js-bulk-action-error').hide();
        $('.js-select-user-error').hide();
        $('.js-bulk-action-dropdown').removeClass('uk-form-danger');

        let bulkAction = $('.js-bulk-action-dropdown').val();
        let users = $('input[name="user[]"]:checked')
        let userCount = users.length;

        if (userCount === 0) {
            $('.js-select-user-error').show();
            $('.js-bulk-action-dropdown').addClass('uk-form-danger');
            return;
        }

        if (!bulkAction) {
            $('.js-bulk-action-error').show();
            $('.js-bulk-action-dropdown').addClass('uk-form-danger');
            return;
        }

        let data = {userCount: userCount};

        if (userCount === 1) {
            data.userId = users[0].value;
        }

        $.ajax({
            url: bulkAction,
            method: 'GET',
            data: data
        }).then(this.modalRenderSuccessHandler).catch((jqXHR) => {
            const errorData = JSON.parse(jqXHR.responseText);
        });
    }

    handleBulkActionFormSubmit(event) {

        if (event.cancelable) {
            event.preventDefault();
        }

        let form = $(event.target).get(0);
        let url = $(event.target).attr('action');

        var formData = new FormData(form);

        let users = $('input[name="user[]"]:checked')
        users.each((i, obj) => {
            formData.append('userIds[]', obj.value);
        });

        $.ajax({
            url: url,
            data: formData,
            contentType: false,
            processData: false,
            type: "POST"
        }).then(this.formSubmitSuccessHandler).catch(this.formSubmitErrorHandler);

    }

    _setSelectedUsers() {
        let numberOfSelectedUsers = $('[name="user[]"]:checked').length;

        if (numberOfSelectedUsers > 0) {
            $('.js-total-users-selected').html(`(${numberOfSelectedUsers} selected)`);
        } else {
            $('.js-total-users-selected').html('');
        }
    }
}


export default ManageUsersComponent;