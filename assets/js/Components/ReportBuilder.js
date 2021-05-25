'use strict';

import $ from 'jquery';
import Routing from '../Routing';
import _ from 'lodash';

class ReportBuilder {

    // Returns the filter struct for the corresponding ID (e.g. "self.created" -> {'id','type','operators'}).
    getFilter(id) {
        let parts = id.split('.');
        let entityName = this.reportEntityName;
        for(var i = 0; i < parts.length - 1; i++) {
            entityName = this.metadata[entityName]['related_entities'][parts[i]]['association_class'];
        }
        // clone it
        let filter = Object.assign({}, this.metadata[entityName]['filters'][parts[parts.length - 1]]);
        filter.id = id;
        filter.description = this.getFilterDescription(id);
        return filter;
    }
    
    // e.g. "self.created" -> "Request > 1:1 Self"
    getFilterDescription(id) {
        let parts = id.split('.');
        let output = [];
        let entityName = this.reportEntityName;
        
        output.push(this.metadata[entityName].pretty_class_name);
        
        for(var i = 0; i < parts.length - 1; i++) {
            let next = this.metadata[entityName]['related_entities'][parts[i]];
            output.push(next.column_human_readable_name); // match option label vs the joined entity name
            entityName = next.association_class;
        }
        
        return output.join(' > ');
    }
    
    // Returns the prefix for ID (e.g. "self.records.created" -> "self.records")
    getPrefix(id) {
        let parts = id.split('.');
        if (parts.length > 1) {
            parts.pop();
            return parts.join('.');
        }
        return null;
    }
    
    // Applys a prefix to an ID (e.g. "created", "self" -> "self.created")
    prefixed(id, prefix = null) {
        return prefix ? [prefix, id].join('.') : id;
    }

    defineFilterIfNotExist(id) {
        if (!this.filters.some(e => e.id === id)) {
            let filter = this.getFilter(id);
            console.log("addFilter", filter.id);
            this.filters.push(filter);
            // setFilters() here would be convenient but it gets weird behavior
            // during initialization or select option building events
        }
    }


    /**
     * @param $wrapper
     * @param globalEventDispatcher
     */
    constructor($wrapper, globalEventDispatcher) {
        this.$wrapper = $wrapper;

        console.log("report builder");

        if (this.$wrapper.length == 0) {
            return;
        }
        
        this.globalEventDispatcher = globalEventDispatcher;
        //this.reportId = this.$wrapper.attr('data-report');
        //this.reportEntityId = this.$wrapper.attr('data-report-entity');
        //this.reportEntityName = this.$wrapper.data('entity-name');
        this.columnPrettyNames = [];
        this.filters = [];
        this.metadata = []; // THE BIG KAHUNA CACHE className => [filters, related_entities]
        this.currentRule = [];
        
        this.unbindEvents();
        this.bindEvents();
        
        
/*        if (this.reportEntityName) {

            // metadata[]
            // column_ids[]
            // filter_ids[]
            this.context = this.$wrapper.data('context');
            let self = this;

            this.metadata = this.context.metadata;
            this.filters = [];
            
            // base filters
            $.each(self.metadata[this.reportEntityName].filters, function(i, e) {
                self.defineFilterIfNotExist(e.id); 
            });
            
            // filters from filters (essentially anything shown in filter selects)
            $.each(this.context.filter_ids, function(id, className) {
                let prefix = self.getPrefix(id); // "self"
                $.each(self.metadata[className].filters, function(i, e) {
                    self.defineFilterIfNotExist(self.prefixed(e.id, prefix)); // "self.someOtherAttr"
                });
            });
            
            console.log("metadata", this.metadata);
            console.log("filters", this.filters);
            
            let rules = JSON.parse(this.$wrapper.find('#report_rules').val());
            this.initializeQueryBuilder(this.filters);
            this.setQueryBuilderRules(rules);
            this._renderSelectColumnsDropdown(this.reportEntityName);
            
            if (this.$wrapper.find('#js-selected-columns-sortable').children().length) {
                this.$wrapper.find('#js-selected-columns').show();
            }
            
            // for "where in (?,?)" queries (enumerated as rulegroup OR).. same filter may show up twice
            for (var i = 0; i < this.context.filter_ids_flat.length; i++) {
                var id = this.context.filter_ids_flat[i];
                var className = this.context.filter_ids[id];
                var $filter = $(`select[name="builder_rule_${i}_filter"]`);
                self.setFilterOptions($filter, className, self.getPrefix(id));
                $filter.val(id);
            }
            
            $('.js-selected-column-data').each(function() {
                let field = JSON.parse($(this).find('.col-field').val());
                $(this).find('.col-name').attr('title', self.getFilterDescription(field.column))
            });
        }*/
        
        //this.initializeSelectizeField('.js-selectize-personGroups');
        //this.initializeSortableColumnsList();
    }
    
    
    // columns and filters selects are the same thing.. 
    setFilterOptions($select, className, prefix = null) {
        let self = this;
        
        console.log("setFilterOptions", $select.attr('name'), className, prefix);
        
        //$select.attr('title', this.getFilterDescription(prefix ? prefix+'.fake' : '')); // the last .thing is an association
        $select.attr('data-entity-name', className);
        $select.attr('data-prefix', prefix);
        
        $select.empty();
        
        let $group1 = $("<optgroup label='Properties'>");
        let $group2 = $("<optgroup label='Related Entities'>");  

        $group1.append(`<option value="-1">---</option>`);
        $.each(this.metadata[className].filters, function(i, e) {
            let filterName = self.prefixed(e.id, prefix);
            self.defineFilterIfNotExist(filterName);
            $group1.append(`<option value="${filterName}">${e.label}</option>`);
        });

        $.each(this.metadata[className].related_entities, function(i, e) {
            $group2.append(`<option class="js-related-entity" 
                data-entity-name="${e.association_class}"
                value="${self.prefixed(e.column_machine_name, prefix)}">${e.column_human_readable_name}
                </option>`);
        });
        
        $select.append($group1, $group2);

        return $select;
    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            queryBuilder: '#builder',
            entitySelect: '.js-entity',
            reportForm: '.js-report-form',

            /*

            reportColumnsDiv: '#js-entity-columns',
            reportColumnsSelect: '#report_columns',
            selectedColumnsDiv: '#js-selected-columns',
            selectedColumnsList: '#js-selected-columns-sortable',
            addColumnButton: '#js-report-add-column-button',
            removeColumnButton: '.js-report-remove-column-button',
            resetColumnsButton: '.reset-columns-dropdown-btn'
            */
        }
    }

    /**
     * bindEvents
     * @returns {ReportBuilder}
     */
    bindEvents() {

        /*
        this.$wrapper.on('click', ReportBuilder._selectors.addColumnButton, this.addColumnHandler.bind(this));
        this.$wrapper.on('click', ReportBuilder._selectors.removeColumnButton, this.removeColumnHandler.bind(this));
        this.$wrapper.on('click', ReportBuilder._selectors.resetColumnsButton, this.resetColumnsDropdownClickHandler.bind(this));
        */

        this.$wrapper.on('change', ReportBuilder._selectors.entitySelect, this.entityChangedHandler.bind(this));
        this.$wrapper.on('submit', ReportBuilder._selectors.reportForm, this.handleReportFormSubmit.bind(this));

        /*
        this.$wrapper.on('change', ReportBuilder._selectors.reportColumnsSelect, this.columnsChangedHandler.bind(this));

        */
        return this;
    }

    /**
     * unbindEvents
     * @returns {ReportBuilder}
     */
    unbindEvents() {

        /*
        this.$wrapper.off('click', ReportBuilder._selectors.addColumnButton);
        this.$wrapper.off('click', ReportBuilder._selectors.removeColumnButton);
        this.$wrapper.off('click', ReportBuilder._selectors.resetColumnsButton);
        */

        this.$wrapper.off('change', ReportBuilder._selectors.entitySelect);
        this.$wrapper.off('submit', ReportBuilder._selectors.reportForm);

        /*
        this.$wrapper.off('change', ReportBuilder._selectors.reportColumnsSelect);
        */
        return this;
    }

    addSelectChangeFilterEvent(e) {
        
        let $select = $(e.target);
        let $optionSelected = $("option:selected", $select);
        let builder = ReportBuilder._selectors.queryBuilder;
        let $builder = $(builder)[0].queryBuilder;
        let $rule = $select.closest('.rule-container');
        let currentRule = $builder.getModel($rule);

        if(!$optionSelected.hasClass('js-related-entity')) {
            $builder.getModel($rule).filter = $builder.getFilterById($select.val());
        } else {
            let entityName = $optionSelected.data('entity-name');
            let prefix = $optionSelected.val();
            this.currentRule = currentRule;
            
            // This is the main point
            this.loadRelatedEntityColumns(entityName, prefix).then((data) => {
                // setting filter option html here doesn't work
            });
        }
    }


    setRuleFiltersSelect(e, rule) {
        if(this.currentRule.id === rule.id) {
            rule.$el.find('[name="'+ rule.id + '_filter"]').val('-1').change();
        }
    }
    

    /**
     * Update the html for the select filters
     * @param e
     * @param rule
     * @param filters
     */
    modifyFilterListSelectHandler(e, rule, filters) {

        let $select = $(e.value); // do not trust

        $select.addClass('uk-select');

        // actual event data
        // our .change() can't seem to overwrite html afterwards so.. do this here
        //var $actualSelect = $('[name="'+ rule.id + '_filter"]');
        //var $selectedOption = $('[name="'+ rule.id + '_filter"]').find('option:selected');
        
        //var className = null;
        //var prefix = null;
        
       /* if ($selectedOption.hasClass('js-related-entity')) {
            className = $selectedOption.data('entity-name');
            prefix = $selectedOption.val();
        } else {
            className = $actualSelect.data('entity-name') ?? this.reportEntityName;
            prefix = $actualSelect.data('prefix');
        }
        */
        //console.log("modify", e, rule, $select.attr('name'), className, prefix);
        //$select = this.setFilterOptions($select, className, prefix);

        e.value = $select.get(0).outerHTML;
    }

    modifyFilterOperatorSelectHandler(e, rule, filters) {
        let $select = $(e.value);
        $select.addClass('uk-select');
        e.value = $select.get(0).outerHTML;
    }

    modifyRuleInputHandler(e, rule, filters) {
        let $input = $(e.value);
        $input.addClass('uk-input');
        e.value = $input.get(0).outerHTML;
    }

    /**
     * Destroys/initializes the query builder and builds
     * or rebuilds the "Columns" dropdown field.
     * @param e
     */
    entityChangedHandler(e) {

        debugger;

        if (e.cancelable) {
            e.preventDefault();
        }

        debugger;

        let $element = $(e.target);
        let entityName = $element.find('option:selected').val();

        this.reportEntityName = entityName;
        this.columnPrettyNames = [];

        //this.$wrapper.find(ReportBuilder._selectors.selectedColumnsList).empty();
        //$(ReportBuilder._selectors.selectedColumnsDiv).hide();

      /*  if(!entityName) {
            this.$wrapper.find(ReportBuilder._selectors.reportColumnsDiv).empty();
            $(ReportBuilder._selectors.queryBuilder).queryBuilder('destroy');
            $(this.$wrapper).find('.empty-query-builder-message').show().find('.alert').removeClass('border-danger text-danger');
            return;
        }*/

        let $builder = $(ReportBuilder._selectors.queryBuilder);
        //$builder.removeClass('qb-initialized');
        //this.metadata = [];
        //this.filters = [];
        
        this.loadRelatedEntityColumns(entityName).then((data) => {
            debugger;
            //this._renderSelectColumnsDropdown(entityName);
        });

    }

    /**
     * Refreshes the "Columns" dropdown menu with options based
     * on a related entity option being selected.
     * @param e
     */
    columnsChangedHandler(e) {

        if (e.cancelable) {
            e.preventDefault();
        }

        let $element = $(e.target);

        let $optionSelected = $("option:selected", $element);
       
        $(ReportBuilder._selectors.addColumnButton).prop('disabled', !$optionSelected.val());

        if(!$optionSelected.hasClass('js-related-entity')) {
            return;
        }

        let entityName = $optionSelected.attr('data-entity-name');
        let prefix = $optionSelected.val();
        
        this.loadRelatedEntityColumns(entityName, prefix).then((data) => {
            this._renderSelectColumnsDropdown(entityName, prefix);
        });
    }

    /**
     * Refreshes the "Columns" dropdown menu with options based
     * on the current selection in the "Entiy" dropdown.
     * @param e
     */
    resetColumnsDropdownClickHandler(e) {
        if (e.cancelable) {
            e.preventDefault();
        }
        let $button = $(e.currentTarget);

        $button.find('.fa-redo-alt').addClass('fa-spin');
        
        let $entityDropdown = $(ReportBuilder._selectors.entitySelect);
        let entityName = $entityDropdown.find('option:selected').data('entity-name');
        
        this.columnPrettyNames = [];
        
        this.loadRelatedEntityColumns(entityName).then((data) => {
            this._renderSelectColumnsDropdown(this.reportEntityName);
        });
    }

    /**
     * Fires when add column button is clicked
     * @param e
     */
    addColumnHandler(e) {
        if (e.cancelable) {
            e.preventDefault();
        }
        let $button = $(e.target);
        let $columnField = $button.parents('#js-entity-columns').find('#report_columns');
        let columnFieldVal = $columnField.val();
        let columnFieldName = $button.parents('#js-entity-columns').find( "#report_columns option:selected" ).text();
        let $parentContainer = $(ReportBuilder._selectors.selectedColumnsList);
        let index = $parentContainer.children('.js-selected-column').length;
        let template = $parentContainer.data('template').replace(/\${index}/g, index).replace(/\${defaultName}/g, columnFieldName);
        let tpl = $.parseHTML(template);
        let $tplContainer = $('<div class="js-selected-column card bg-light mb-2">');
        
        if( !columnFieldVal ) {
            alert('Please select an option from the "Columns" dropdown.');
            return;
        }
        
        $tplContainer.append(tpl)
            .find('.col-name, .col-default-name').val(columnFieldName).end()
            .find('.col-field').val(columnFieldVal).end()
            .find('.col-name').attr('title', this.getFilterDescription(JSON.parse(columnFieldVal).column));
            
        $parentContainer.append($tplContainer);

        $(ReportBuilder._selectors.selectedColumnsDiv).show();
        $('select#report_columns').removeClass('is-invalid');
        $('.js-selected-columns-empty-error').hide();

        $('[data-toggle="tooltip"]').tooltip();
        this.updateColumnSelectDropdown();

    }

    /**
     * Fires when remove column button is clicked
     * @param e
     */
    removeColumnHandler(e) {
        if (e.cancelable) {
            e.preventDefault();
        }
        let $button = $(e.target);
        let $element = $button.closest('.js-selected-column');
        let $container = $element.closest(ReportBuilder._selectors.selectedColumnsList);
        $element.remove();
        this.updateSortableAttributes($container);
        this.updateColumnSelectDropdown();

        if (!$container.children().length) {
            $(ReportBuilder._selectors.selectedColumnsDiv).hide();
        }
    }

    /**
     * Re-index/update attributes for all sortable column list items.
     * @param $container
     */
    updateSortableAttributes($container) {
        $container.find('.js-selected-column').each(function (idx) {
            let $inputs = $(this).find('input');
            let $labels = $(this).find('label');
            $inputs.each(function () {
                this.name = this.name.replace(/(\[\d\])/, '[' + idx + ']');
                this.id = this.id.replace(/(\_\d\_)/, '_' + idx + '_');
            });
            $labels.each(function () {
                this.attributes.for.value = this.attributes.for.value.replace(/(\_\d\_)/, '_' + idx + '_');
            });
        });
    }
    
    /**
     * Enable/disable options in the "Columns" dropdown depending
     * on if they have already been selected or not.
     */
    updateColumnSelectDropdown() {
        let $container = $(ReportBuilder._selectors.selectedColumnsList);
        let selectField = ReportBuilder._selectors.reportColumnsSelect;

        let selectedValues = [];
        $container.find('.js-selected-column').each(function () {
            let selectedValue = $(this).find('input.col-field').val();
            selectedValues.push(selectedValue);
        });

        $(selectField).val('');
        $(ReportBuilder._selectors.addColumnButton).prop('disabled', 'disabled');
        
        $(selectField + ' .js-selectable-columns-group option').each(function () {
            if ($.inArray($(this).val(), selectedValues) != -1) {
                $(this).prop('disabled', true);
            } else {
                $(this).prop('disabled', false);
            }
        });
    }

    /**
     * Setup radio/checkbox fields with custom bootstrap styles
     * @param e
     * @param rule
     */
    customizeRadiosAndCheckboxes(e, rule) {

        if(!rule.filter) {
            return;
        }

        let inputType = rule.filter.input;
        if (inputType === 'radio' || inputType === 'checkbox') {
            let $inputWrap = rule.$el.find('.rule-value-container div.' + inputType);
            $inputWrap.addClass('custom-control custom-' + inputType);
            $inputWrap.find('input').addClass('custom-control-input');
            $inputWrap.find('label').addClass('custom-control-label');
        }
    }

    /**
     * Destroys/initializes the querybuilder js library.
     * @param filters
     */
    initializeQueryBuilder(filters) {
        debugger;
        let builder = ReportBuilder._selectors.queryBuilder;
        $(builder).queryBuilder('destroy');
        //$(this.$wrapper).find('.empty-query-builder-message').hide().find('.alert').removeClass('border-danger text-danger');

        let self = this;
       // let filterSelect = '.rule-filter-container [name$=_filter]';

        $(builder).queryBuilder({
            plugins: [
                /*'sortable',*/
                //'filter-description',
                /*'unique-filter',*/
                //'bt-tooltip-errors',
                //'bt-checkbox',
               /* 'invert',*/
                /*'not-group',*/
            ],
            filters: filters,
            allow_empty: true
        }).on('getRuleInput.queryBuilder.filter', function (e, rule, filters) {
            self.modifyRuleInputHandler(e, rule, filters);
        }).on('getRuleFilterSelect.queryBuilder.filter', function (e, rule, filters) {
            self.modifyFilterListSelectHandler(e, rule, filters);
        }).on('getRuleOperatorSelect.queryBuilder.filter', function (e, rule, filters) {
            self.modifyFilterOperatorSelectHandler(e, rule, filters);
        });

            /*.off('change.queryBuilder', filterSelect).on('change.queryBuilder', filterSelect, function (e) {
            //self.addSelectChangeFilterEvent(e);
        }).on('afterUpdateRuleFilter.queryBuilder afterUpdateRuleOperator.queryBuilder', function (e, rule) {
            //self.customizeRadiosAndCheckboxes(e, rule);
        }).on('afterCreateRuleFilters.queryBuilder', function (e, rule) {
            //self.setRuleFiltersSelect(e, rule);
        }).addClass('qb-initialized');*/
    }

    /**
     * Initializes the jquery sortable libary for selected columns.
     */
    initializeSortableColumnsList() {
        let sortableList = ReportBuilder._selectors.selectedColumnsList;
        $(sortableList).sortable({
            axis: "y",
            cursor: "move",
            handle: ".sort-entity-col-handle",
            items: "> .js-selected-column",
            forcePlaceholderSize: true,
            placeholder: 'sort-entity-col-placeholder card mb-2',
            stop: function( event, ui ) {
                ui.item.removeAttr('style');
            },
            update: function( event, ui ) {
                let $item = ui.item;
                let $container = $item.closest('#js-selected-columns-sortable');
                this.updateSortableAttributes($container);
            }.bind(this)
        });
    }

    /**
     * Initializes the jquery selectize library for the provided field.
     * @param field
     */
    initializeSelectizeField(field) {
        $(field).each((index, element) => {
            if ($(element).selectize()[0].selectize) { // requires [0] to select the proper object
                let value = $(element).val(); // store the current value of the select/input
                $(element).selectize()[0].selectize.destroy(); // destroys selectize()
                $(element).val(value);  // set back the value of the select/input
            } else {
                $(element).selectize()[0].selectize.clear(true);
            }

            $(element).selectize({
                plugins: {
                    'remove_button': { label: '<i class="fal fa-times"></i>' }
                },
                sortField: 'text'
            });
        });
    }

    /**
     * Sets rules for querybuilder when on edit form or invalid submission.
     * @param rules
     */
    setQueryBuilderRules(rules) {
        let builder = ReportBuilder._selectors.queryBuilder;
        $(builder).queryBuilder('setRules', rules);
    }

    /**
     * Display entity columns select field based on chosen entity.
     * @private
     * @param data
     * @param associationMap
     */
    _renderSelectColumnsDropdown(entityName, prefix = null) {

        let options = ``;
        let related_entity_options = ``;
        let self = this;
        
        $.each(this.metadata[entityName].filters, function(i, e) {
            var json = JSON.stringify({
                'column': self.prefixed(e.id, prefix)
            });
            
            options += `<option value=${json}>${e.label}</option>`;
        });
        
        $.each(this.metadata[entityName].related_entities, function(i, e) {
            related_entity_options += `<option 
                class="js-related-entity" 
                data-entity-name="${e.association_class}"
                value="${self.prefixed(e.column_machine_name, prefix)}">${e.column_human_readable_name}
                </option>`;
        });

        const html = columnsTemplate(options, related_entity_options);
        const $reportColumnsTemplate = $($.parseHTML(html));
        
        this.columnPrettyNames.push(this.metadata[entityName].pretty_class_name);
        $reportColumnsTemplate.find('.textPrettyColumnNames').text(this.columnPrettyNames.join(' > '));
        $reportColumnsTemplate.find(ReportBuilder._selectors.addColumnButton).prop('disabled', 'disabled');
        
        this.$wrapper.find(ReportBuilder._selectors.reportColumnsDiv).html($reportColumnsTemplate);
        this.updateColumnSelectDropdown();
    }
    
   /**
    * Loads filters for query builder from server, based on building
    * block from entity select field or reportEntityId.
    * @returns Promise
    */
   loadRelatedEntityColumns(entityName, prefix = null) {
       debugger;
       return new Promise((resolve, reject) => {

           debugger;

           const url = Routing.generate('report_related_entity_columns');
           $.ajax({
               url: url,
               data: {
                   entity: entityName
               },
               async: true
           }).then(data => {

               debugger;

               // add to metadata (index by ID)
               this.metadata[entityName] = {
                   'filters': {},
                   'related_entities': {},
                   'pretty_class_name': data.data.pretty_class_name
               };
               
               for (let filter of data.data.filters) {
                   this.metadata[entityName].filters[filter.id] = filter;
                   
                   // auto-define new filters
                   let id = this.prefixed(filter.id, prefix);
                   this.defineFilterIfNotExist(id);
               }
               
               for (let relatedEntity of data.data.related_entities) {
                   this.metadata[entityName].related_entities[relatedEntity.column_machine_name] = relatedEntity;
               }
               
               let $builder = $(ReportBuilder._selectors.queryBuilder);
               if ($builder.hasClass('qb-initialized')) {
                   $builder.queryBuilder('setFilters', this.filters);
               } else {
                   debugger;
                   this.initializeQueryBuilder(this.filters);
                   $builder.queryBuilder('setFilters', this.filters);
               }
               
               //console.log("api", this.metadata[entityName]);
               
               resolve(data);
           }).catch(jqXHR => {
               debugger;
               console.log(jqXHR);
               const errorData = JSON.parse(jqXHR.responseText);
               reject(errorData);
           });
       });
   }

    /**
     * Catch the report form submission, do some work, then carry on with submit.
     * @param e
     */
    handleReportFormSubmit(e) {

        debugger;

        // TODO GET THE COLUMNS WIRED IN (Might be a heavy lift as I'm not exactly sure how this was done before with the entity associations/relationships, etc)
        //  GET THE REPORT SAVING
        //  GET A DOWNLOAD ENDPOINT WIRED UP
        //  ADD ADDITIONAL ENTITIES TO THE REPORT
        e.preventDefault();
        let builder = ReportBuilder._selectors.queryBuilder;
        let reportForm = ReportBuilder._selectors.reportForm;
        let $reportColumnsDiv = this.$wrapper.find(ReportBuilder._selectors.reportColumnsDiv);
        let $selectedColumnsList = this.$wrapper.find(ReportBuilder._selectors.selectedColumnsList);

        if ( $reportColumnsDiv.children().length ) {
            if (!$selectedColumnsList.children().length) {
                $('select#report_columns').addClass('is-invalid');
                $('.js-selected-columns-empty-error').show();
                $([document.documentElement, document.body]).animate({
                    scrollTop: $("#js-entity-columns-field-wrap").offset().top
                }, 500);
                return;
            }
        }

        if ( !$(builder).children().length ) {
            $(this.$wrapper).find('.empty-query-builder-message .alert').addClass('border-danger text-danger');
            return;
        } else {
            if( !$(builder).queryBuilder('validate') ) {
                return;
            }
            let rules = $(builder).queryBuilder('getRules');
            this.$wrapper.find('.js-rules').val(JSON.stringify(rules));
        }

        this.$wrapper.off('submit', reportForm);
        $(reportForm).submit();
    }
}

const columnsTemplate = (options, related_entity_options) => `
<div id="js-entity-columns-field-wrap">
    <div class="form-group">
        <label class="field-label" for="report_columns">Columns <small class="text-muted textPrettyColumnNames"></small>
            <span class="invalid-feedback js-selected-columns-empty-error" style="display: none;">
                <span class="d-block">
                    <span class="form-error-icon badge badge-danger text-uppercase">Alert</span> <span class="form-error-message">Please add at least one column to use in this report.</span>
                </span>
            </span>
        </label>
        <div class="reset-and-select-wrap">
            <div class="input-group">
                <select id="report_columns" class="js-entity-column form-control custom-select">
                    <option value="" selected>-- Select a column to add --</option>
                    <optgroup label="Columns" class="js-selectable-columns-group">
                        ${options}
                    </optgroup>
                    <optgroup label="Related Entities">
                        ${related_entity_options}
                    </optgroup>
                </select>
                <div class="input-group-append">
                    <button class="btn btn-outline-secondary reset-columns-dropdown-btn" title="Reset dropdown with base entity values." type="button"><i class="far fa-redo-alt"></i></button>
                </div>
            </div>
        </div>
    </div>
    <button type="button" class="btn btn-outline-primary-inverse" id="js-report-add-column-button">Add Column</button>
</div>
`;

export default ReportBuilder;