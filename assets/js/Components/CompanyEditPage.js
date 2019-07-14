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
        debugger;
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.companies = [];
        this.list = null;

        this.unbindEvents();

        this.bindEvents();

        this.render();
    }

    unbindEvents() {

        this.$wrapper.off('click', CompanyEditPage._selectors.addPhoto);
    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            addPhoto: '.js-addPhoto'
        }
    }

    bindEvents() {

        this.$wrapper.on(
            'click',
            CompanyEditPage._selectors.addPhoto,
            this.handleAddItemButtonClick.bind(this)
        );

    }

    handleAddItemButtonClick(e) {

        debugger;
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

    render() {}

    static markup() {
        return `
            <div class="js-filters">
                <select class="js-primary-industry-filter"></select>
            </div>
            
            <div id="hacker-list">
                <ul class="paginationTop"></ul>
                <input class="search" />
                <span class="sort" data-sort="name">Sort by name</span>
                <span class="sort" data-sort="address">Sort by address</span>
                <ul class="list"></ul>
                <ul class="paginationBottom"></ul>
            </div>
    `;
    }
}

export default CompanyEditPage;