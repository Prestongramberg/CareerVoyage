import $ from 'jquery';

require('select2/dist/js/select2.min');
import PrimaryIndustrySelect from "./Components/PrimaryIndustrySelect";

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
        placeholder: "Please select the grades that this topic presentation is suitable for",
        allowClear: true,
        width: '100%'
    });


    $('#lesson_primaryCourse').select2({
        placeholder: "Please select a primary course",
        allowClear: true,
        width: '100%'
    });

    $('#lesson_secondaryIndustries').select2({
        placeholder: "Please select career field(s)",
        allowClear: true,
        width: '100%',
        "language": {
            "noResults": function(){
                return "Please choose your lesson's industry sector first";
            }
        },
    });

    $('#lesson_secondaryCourses').select2({
        placeholder: "Please select secondary course(s)",
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

    new PrimaryIndustrySelect($('.js-form'), window.globalEventDispatcher);

});