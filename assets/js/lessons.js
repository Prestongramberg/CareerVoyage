import $ from 'jquery';

require('select2/dist/js/select2.min');
import PrimaryIndustrySelect from "./Components/PrimaryIndustrySelect";
import ResourceComponent from "./Components/ResourceComponent";

/*jslint browser: true*/
/*global define, module, exports*/
(function (root, factory) {
    "use strict";
    if (typeof define === 'function' && define.amd) {
        define([], factory);
    } else if (typeof exports === 'object') {
        module.exports = factory();
    } else {
        root.VCountdown = factory();
    }
}(window, function () {
    "use strict";

    var VCountdown = function (options) {
        if (!this || !(this instanceof VCountdown)) {
            return new VCountdown(options);
        }

        if (!options) {
            options = {};
        }

        if (!options.target) {
            throw 'Provide a target to count characters';
        }

        this.target   = document.querySelector(options.target);
        this.maxChars = options.maxChars || 140;

        this.countdown();
    };

    VCountdown.prototype = {
        hasClass: function (el, name) {
            return new RegExp('(\\s|^)' + name + '(\\s|$)').test(el.className);
        },
        addClass: function (el, name) {
            if (!this.hasClass(el, name)) {
                el.className += (el.className ? ' ' : '') + name;
            }
        },
        removeClass: function (el, name) {
            if (this.hasClass(el, name)) {
                el.className = el.className.replace(new RegExp('(\\s|^)' + name + '(\\s|$)'), ' ').replace(/^\s+|\s+$/g, '');
            }
        },
        createEls: function (name, props) {
            var el = document.createElement(name), p;
            for (p in props) {
                if (props.hasOwnProperty(p)) {
                    el[p] = props[p];
                }
            }
            return el;
        },
        insertAfter: function (referenceNode, newNode) {
            referenceNode.parentNode.insertBefore(newNode, referenceNode.nextSibling);
        },
        update: function () {
            var target = this.target,
                currentCount = target.value.length,
                remaining    = this.maxChars - currentCount;

            if (remaining > 10) {
                this.removeClass(target.nextElementSibling, 'warn');
            } else {
                this.addClass(target.nextElementSibling, 'warn');
            }

            target.nextElementSibling.innerHTML = remaining;
        },
        setMaxChars: function () {
            this.target.setAttribute('maxlength', this.maxChars);
        },
        charsLen: function () {
            var span = this.createEls('span', {className: 'chars-length'});
            span.innerHTML = this.maxChars;

            this.insertAfter(this.target, span);

            this.update();
        },
        countdown: function () {
            this.setMaxChars();
            this.charsLen();

            this.target.addEventListener('keyup', this.update.bind(this), false);
        }
    };

    return VCountdown;
}));

$(document).ready(function () {


    $('.js-select2').select2({
        width: '100%'
    });

    $('#lesson_grades').select2({
        placeholder: "Please select the relevant grades that this topic presentation is suitable for",
        allowClear: true,
        width: '100%'
    });


    $('#lesson_primaryIndustries').select2({
        placeholder: "Please select the relevant industry sectors for this topic presentation",
        allowClear: true,
        width: '100%'
    });

    $('#lesson_primaryCourses').select2({
        placeholder: "Please select the relevant school courses for this topic presentation",
        allowClear: true,
        width: '100%'
    });

    VCountdown({
        target: '#lesson_title',
        maxChars: 70
    });

    VCountdown({
        target: '#lesson_shortDescription',
        maxChars: 280
    });

    UIkit.util.on('.uk-switcher', 'show', function (ev) {

        if ($(ev.target).hasClass('lesson_general')) {
            location.hash = 'general';
        }

        if ($(ev.target).hasClass('lesson_attachments')) {
            location.hash = 'attachments';
        }
    });

    new PrimaryIndustrySelect($('.js-form'), window.globalEventDispatcher);
    new ResourceComponent($('.js-resource-component'), window.globalEventDispatcher);

});