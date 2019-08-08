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
require('./vendor/moment.js');
require('./vendor/jquery-datetimepicker.js');
require('./vendor/fontawesome.js');

// Binds to Window
window.globalEventDispatcher = new EventDispatcher();
window.UIkit = UIkit;
window.Pintex = {
    notification: function(message, status = null) {
        UIkit.notification({
            message: message,
            pos: 'bottom-center',
            status: status,
            timeout: 2500
        });
    },
    modal: {
        dynamic_open: function(html) {
            const $modal = $('#global-modal');
            $modal.find('.uk-modal-body').html( html );
            UIkit.modal( $modal ).show();
        },
        close: function() {
            const $modal = $('#global-modal');
            UIkit.modal( $modal ).hide();
        },
        target: "#global-modal"
    }
};

// React
require('./react/root');

// Custom
require('./Custom');