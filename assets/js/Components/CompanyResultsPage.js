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
            console.log(data);
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

        let companies = this.companies = data.data;

       /* for(let company of companies) {
            this.$wrapper.find('.list').append(cardTemplate(company));
        }
*/
        //this.$wrapper.find('.list').append(cardTemplate());

        let options = {
            item: "hacker-item",
            valueNames: [
                'name',
                'shortDescription'
                /*{ name: 'industry', attr: 'data-industry' }*/
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

        this.list = new List('hacker-list', options, companies);

        // this.list.on('searchComplete', () => {
        //     this.$wrapper.find('.list').find('.card').first().remove();
        // });
        //
        // this.$wrapper.find('.list').find('.card').first().remove();
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
            <div class="uk-grid-small uk-flex-middle" uk-grid>
                <div class="uk-width-1-1 uk-width-1-1@s uk-width-1-3@l">
                    <form class="uk-search uk-search-default uk-width-1-1">
                        <span uk-search-icon></span>
                        <input class="uk-search-input search" type="search" placeholder="Search by Name...">
                    </form>
                </div>
                <div class="uk-width-1-1 uk-width-1-2@s uk-width-1-3@l js-filters">
                    <div class="uk-width-1-1 uk-text-truncate" uk-form-custom="target: > * > span:first-child">
                        <select class="js-primary-industry-filter"></select>
                        <button class="uk-button uk-button-default uk-width-1-1 uk-width-autom@l" type="button" tabindex="-1">
                            <span></span>
                            <span uk-icon="icon: chevron-down"></span>
                        </button>
                    </div>
                </div>
                <div class="uk-width-1-1 uk-width-1-2@s uk-width-1-3@l js-filters">
                    <div class="uk-width-1-1 uk-text-truncate" uk-form-custom="target: > * > span:first-child">
                        <select>
                            <option value="">Filter by Experiences...</option>
                            <option value="1">Hosting Site Visits</option>
                            <option value="2">Hosting Experiences</option>
                            <option value="3">Job Opportunities</option>
                            <option value="4">Externships Available</option>
                            <option value="5">Internships Available</option>
                        </select>
                        <button class="uk-button uk-button-default uk-width-1-1 uk-width-autom@l" type="button" tabindex="-1">
                            <span></span>
                            <span uk-icon="icon: chevron-down"></span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="uk-grid" uk-grid>
                <div class="uk-width-1-1 company-listings">

                    <!-- A template element is needed when list is empty, TODO: needs a better solution -->
                    <li id="hacker-item" class="card">
                     <span class="name"></span>
                     <span class="shortDescription"></span>
                    </li>

                </div>
            </div>

            <div class="uk-grid" uk-grid>
                <div class="uk-width-1-1">
                    <ul class="uk-pagination uk-margin">
                        <li><a href="#"><span class="uk-margin-small-right" uk-pagination-previous></span> Previous</a></li>
                        <li class="uk-margin-auto-left"><a href="#">Next <span class="uk-margin-small-left" uk-pagination-next></span></a></li>
                    </ul>
                </div>
            </div>
    `;
    }
}

const selectOptionTemplate = (value) => `
    <option value="${value}">${value}</option>
`;

const cardTemplate = () => `
    <li class="card" data-id="1">
        <div class="uk-card uk-card-default uk-grid-collapse uk-flex-center uk-margin" uk-grid>
            <div class="uk-card-media-left uk-width-1-1 uk-width-medium@m">
                <div class="company-listing__image uk-height-1-1 uk-flex uk-flex-right uk-flex-bottom uk-background-cover uk-light" data-src="images/company-vomela.jpg" uk-img style="min-height: 150px;">
                    <div class="uk-inline uk-padding-small">
                        <a href="#">
                            <i class="fa fa-heart" aria-hidden="true"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="uk-width-1-1 uk-width-expand@m">
                <div class="uk-card-body">
                    <div class="company-listing__meta">
                        <a href="companies-detail.php">
                            <h3 class="uk-card-title-small uk-heading-divider">
                                <span class="name"></span>
                            </h3>
                        </a>
                        <p><span class="shortDescription"></span></p>
                        <div class="uk-grid uk-flex-middle" uk-grid>
                            <div class="uk-width-auto">
                                <div class="company-links">
                                    <a href="" class="uk-icon-button uk-margin-small-right" uk-icon="world"></a>
                                    <a href="" class="uk-icon-button uk-margin-small-right" uk-icon="receiver"></a>
                                    <a href="" class="uk-icon-button uk-margin-small-right" uk-icon="mail"></a>
                                    <a href="" class="uk-icon-button uk-margin-small-right" uk-icon="linkedin"></a>
                                </div>
                            </div>
                            <div class="uk-width-expand uk-visible@m">
                                <div class="uk-align-right">
                                    <a href="companies-detail.php" class="uk-button uk-button-small uk-button-text uk-text-muted">More info</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </li>
`;

export default CompanyResultsPage;