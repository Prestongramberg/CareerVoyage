'use strict';

import $ from 'jquery';
import List from "list.js";
import Routing from "../Routing";

class CompanyResultsPage {

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

        this.$wrapper.off('change', CompanyResultsPage._selectors.primaryIndustryFilter);
    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            primaryIndustryFilter: '.js-primary-industry-filter'
        }
    }

    bindEvents() {

        this.$wrapper.on(
            'change',
            CompanyResultsPage._selectors.primaryIndustryFilter,
            this.handlePrimaryIndustryFilterChange.bind(this)
        );
    }

    handlePrimaryIndustryFilterChange(e) {

        let value = $(e.target).val();

        if("" === value) {
            this.list.filter();
            return;
        }

        this.list.filter((item) => {

            if(!_.has(item.values(), 'primaryIndustry.name')) {
                return false;
            }

            if (item.values().primaryIndustry.name === value) {
                return true;
            } else {
                return false;
            }
        });

    }

    render() {
        this.$wrapper.html(CompanyResultsPage.markup(this));
        this.loadCompanies().then(data => {
            this.renderCompanies(data);
        });

        this.loadIndustries().then(data => {

            this.$wrapper.find(CompanyResultsPage._selectors.primaryIndustryFilter).html("");

            let industries = data.data;

            this.$wrapper.find(CompanyResultsPage._selectors.primaryIndustryFilter).append('<option value="" selected>Select an industry</option>');
            for(let industry of industries) {
                this.$wrapper.find(CompanyResultsPage._selectors.primaryIndustryFilter).append(selectOptionTemplate(industry.name));
            }

        });
    }

    renderCompanies(data) {

        debugger;
        let companies = this.companies = data.data;

        for(let company of companies) {
            this.$wrapper.find('.list').append(cardTemplate(company));
        }

        let options = {
            valueNames: [
                'name',
                /*'born',
                { data: ['id'] },
                { name: 'timestamp', attr: 'data-timestamp' },
                { name: 'link', attr: 'href' },
                { name: 'image', attr: 'src' }*/
            ],
            page: 5,
            pagination: [{
                name: "paginationTop",
                paginationClass: "paginationTop",
                innerWindow: 3,
                left: 2,
                right: 4
            }, {
                paginationClass: "paginationBottom",
                innerWindow: 3,
                left: 2,
                right: 4
            }]
        };

        this.list = new List('hacker-list', options);
    }

    loadCompanies() {
        return new Promise((resolve, reject) => {
            let url = Routing.generate('get_companies');

            $.ajax({
                url: url,
            }).then(data => {
                resolve(data);
            }).catch(jqXHR => {
                const errorData = JSON.parse(jqXHR.responseText);
                reject(errorData);
            });
        });
    }


    loadIndustries() {
        return new Promise((resolve, reject) => {
            let url = Routing.generate('get_industries');

            $.ajax({
                url: url,
            }).then(data => {
                resolve(data);
            }).catch(jqXHR => {
                const errorData = JSON.parse(jqXHR.responseText);
                reject(errorData);
            });
        });
    }


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

const selectOptionTemplate = (value) => `
    <option value="${value}">${value}</option>
`;

const cardTemplate = ({name}) => `
    <li data-id="1">
     <p class="name">${name}</p>
   </li>
`;

export default CompanyResultsPage;