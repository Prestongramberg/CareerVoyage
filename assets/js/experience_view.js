import $ from 'jquery';
import ClipboardJS from 'clipboard/dist/clipboard.min';

$(document).ready(function () {

    console.log("experience view page");
    new ClipboardJS('.js-copy-to-clipboard-btn');

});