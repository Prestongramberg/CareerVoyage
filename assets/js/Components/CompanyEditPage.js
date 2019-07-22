'use strict';

import $ from 'jquery';
import List from "list.js";
import Routing from "../Routing";

class CompanyEditPage {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     */
    constructor($wrapper, globalEventDispatcher) {

        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.companies = [];
        this.list = null;

        this.unbindEvents();

        this.bindEvents();

        this.render();
    }

    unbindEvents() {
        this.$wrapper.off('click', CompanyEditPage._selectors.addVideo);
        this.$wrapper.off('click', CompanyEditPage._selectors.removePhoto);
        this.$wrapper.off('click', CompanyEditPage._selectors.addResource);
    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            addVideo: '.js-addVideo',
            newVideoFields: '.js-new-video-fields',
            removePhoto: '.js-removePhoto',
            addResource: '.js-addResource'
        }
    }

    bindEvents() {

        this.$wrapper.on(
            'click',
            CompanyEditPage._selectors.addVideo,
            this.handleAddItemButtonClick.bind(this)
        );

        this.$wrapper.on(
            'click',
            CompanyEditPage._selectors.removePhoto,
            this.handleRemovePhoto
        );

        this.$wrapper.on(
            'click',
            CompanyEditPage._selectors.addResource,
            this.handleAddResourceItemButtonClick.bind(this)
        );
    }

    handleRemovePhoto(e) {

        const $this = $(this);
        const endpoint = $this.attr('data-remove');

        console.log(endpoint);

        $.ajax({
            url: endpoint,
        }).then(data => {
            $this.parent().remove();
            Pintex.notification("Photo Removed!");
        }).catch(jqXHR => {
            const errorData = JSON.parse(jqXHR.responseText);
            console.log(errorData);
        });
    }

    handleAddItemButtonClick(e) {

        if(e.cancelable) {
            e.preventDefault();
        }

        let $parentContainer = $('.js-parent-container');
        let index = $parentContainer.children('.js-child-item').length;
        let template = $parentContainer.data('template');
        let tpl = eval('`'+template+'`');
        let $container = $('<li>').addClass('list-group-item js-child-item');
        $container.append(tpl);
        $parentContainer.append($container);
    }

    handleAddResourceItemButtonClick(e) {

        debugger;
        if(e.cancelable) {
            e.preventDefault();
        }

        let $parentContainer = $('.js-add-resource-parent-container');
        let index = $parentContainer.children('.js-child-item').length;
        let template = $parentContainer.data('template');
        let tpl = eval('`'+template+'`');
        let $container = $('<li>').addClass('list-group-item js-child-item');
        $container.append(tpl);
        $parentContainer.append($container);
    }

    render() {}
}

export default CompanyEditPage;