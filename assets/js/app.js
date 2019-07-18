'use strict';

// App SCSS
require('../css/app.scss');

import EventDispatcher from "./EventDispatcher";
import UIkit from 'uikit';
import Icons from 'uikit/dist/js/uikit-icons';

// loads the Icon plugin
UIkit.use(Icons);

// App Vendor JS
const $ = require('jquery');
require('./vendor/fontawesome.js');
let _ = require('lodash');

window.globalEventDispatcher = new EventDispatcher();

// App Custom JS
$(document).ready(function() {
    $('body').addClass('ready');
});