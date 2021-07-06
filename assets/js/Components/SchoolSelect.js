'use strict';

import $ from 'jquery';
import List from "list.js";
import Routing from "../Routing";

class SchoolSelect {

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
        this.$wrapper.off('change', SchoolSelect._selectors.school);
    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            school: '.js-school'
        }
    }

    bindEvents() {

        this.$wrapper.on(
            'change',
            SchoolSelect._selectors.school,
            this.handleSchoolChange.bind(this)
        );
    }

    handleSchoolChange(e) {

        debugger;
        if (e.cancelable) {
            e.preventDefault();
        }

        this.initMarkers();
    }

    render() {
    }
}

export default SchoolSelect;