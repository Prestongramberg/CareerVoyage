'use strict';

// App SCSS
require('../css/app.scss');

import EventDispatcher from "./EventDispatcher";
import UIkit from 'uikit';
import Icons from 'uikit/dist/js/uikit-icons';
import Quill from 'quill';

// loads the Icon plugin
UIkit.use(Icons);

// App Vendor JS
const $ = require('jquery');
require('./vendor/fontawesome.js');

// Binds to Window
window.globalEventDispatcher = new EventDispatcher();
window.UIkit = UIkit;

// App Custom JS
$(document).ready(function() {

    // Smooth Page Transitions
    $('body').addClass('ready');

    // WYSIWYG
    let instance = 1;
    $('[data-wysiwyg]').each(function(){
        (function($elem) {

            // Get the Target
            const targetSelector = $elem.attr('data-wysiwyg');
            const $target = $(targetSelector);

            // Add a unique instance class
            const uniqueClass = 'wysiwyg-editor-' + instance;
            $elem.addClass(uniqueClass);

            // Instantiate the Editor
            const editor = new Quill(`.${uniqueClass}`, {
                theme: 'snow'
            });

            // Populate the editor with any content that already exists from target
            editor.root.innerHTML = $target.val();

            // Listen for events and update the target
            editor.on('text-change', function(delta, oldDelta, source) {
                $target.val(
                    editor.root.innerHTML
                );
            });

            // Increment the unique instance
            instance++;

        })($(this));
    });

    // Upload Progress
    $('.js-upload[data-name]').each(function(){
        (function($elem) {

            UIkit.upload($elem, {

                multiple: true,

                beforeSend: function () {
                    console.log('beforeSend', arguments);
                },
                beforeAll: function () {
                    console.log('beforeAll', arguments);
                    console.log(arguments[1]);
                },
                load: function () {
                    console.log('load', arguments);
                },
                error: function () {
                    console.log('error', arguments);
                },
                complete: function () {
                    console.log('complete', arguments);
                },

                loadStart: function (e) {
                    console.log('loadStart', arguments);
                },

                progress: function (e) {
                    console.log('progress', arguments);
                },

                loadEnd: function (e) {
                    console.log('loadEnd', arguments);
                },

                completeAll: function () {
                    console.log('completeAll', arguments);
                }

            });

        })($(this));
    });

});