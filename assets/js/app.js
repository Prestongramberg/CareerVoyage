'use strict';

// App SCSS
require('../css/app.scss');

// App Vendor JS
const $ = require('jquery');
require('./vendor/uikit.js');
require('./vendor/uikit-icons.js');
require('./vendor/fontawesome.js');

// App Custom JS
$(document).ready(function() {
    $('body').addClass('ready');
});