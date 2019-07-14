'use strict';

// App SCSS
import EventDispatcher from "./EventDispatcher";

require('../css/app.scss');

// App Vendor JS
const $ = require('jquery');
require('./vendor/uikit.js');
require('./vendor/uikit-icons.js');
require('./vendor/fontawesome.js');
let _ = require('lodash');

window.globalEventDispatcher = new EventDispatcher();

// App Custom JS