import $ from 'jquery';
import Pusher from 'pusher-js';
import ChatWidget from "./Components/ChatWidget";

$(document).ready(function() {
    new ChatWidget($('.js-chats-landing'), window.globalEventDispatcher);
});