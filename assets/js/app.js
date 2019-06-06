'use strict';
/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you require will output into a single css file (app.css in this case)
import BingoApp from "./Components/BingoApp";

require('../css/app.scss');

// Need jQuery? Install it with "yarn add jquery", then uncomment to require it.
const $ = require('jquery');
import 'bootstrap';
import EventDispatcher from './EventDispatcher';

window.globalEventDispatcher = new EventDispatcher();
/*global.$ = $;*/


/*import RepLogApp from './Components/RepLogApp';*/

$(document).ready(function() {
    new BingoApp($('#app'), window.globalEventDispatcher);
});

