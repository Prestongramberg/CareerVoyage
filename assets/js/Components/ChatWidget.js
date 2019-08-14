'use strict';

import $ from 'jquery';
import List from "list.js";
import Routing from "../Routing";
import Pusher from 'pusher-js';

class ChatWidget {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     */
    constructor($wrapper, globalEventDispatcher) {

        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.chat = {};

        this.unbindEvents();
        this.bindEvents();
    }

    unbindEvents() {
        this.$wrapper.off('click', ChatWidget._selectors.createChatButton);
    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            createChatButton: '.js-create-chat',
            message: '.js-message',
            sendMessageButton: '.js-send-message-button',
            messages: '.js-messages'

        }
    }

    bindEvents() {

        this.$wrapper.on(
            'click',
            ChatWidget._selectors.createChatButton,
            this.handleCreateChatButtonPressed.bind(this)
        );

        this.$wrapper.on(
            'click',
            ChatWidget._selectors.sendMessageButton,
            this.handleSendMessageButtonPressed.bind(this)
        );
    }

    handleCreateChatButtonPressed(e) {

        debugger;
        if(e.cancelable) {
            e.preventDefault();
        }

        let data = {};
        data.userId = $(e.target).attr('data-userId');

        this._createChat(data).then((data) => {
            debugger;
           this.chat = data.data;

           for(let message of this.chat.messages) {
               this.render(message);
           }

           this._startListening();
        });
    }

   _startListening() {

       Pusher.logToConsole = true;

       const socket = new Pusher('3b8e318e4abe6c429446', {
           cluster: 'us2',
           forceTLS: true
       });

       let channel = socket.subscribe('chat-' + this.chat.uid);

       channel.bind('send-message', (data) => {
           this.chat = data.chat;
           this.render(this.chat.messages.slice(-1)[0]);
       });
   }

   handleSendMessageButtonPressed(e) {

        debugger;
        if(e.cancelable) {
            e.preventDefault();
        }
        let message = this.$wrapper.find(ChatWidget._selectors.message).val();
        let data = {};
        data.message = message;
        this._messageChat(data).then((data) => {});
    }

    _createChat(data) {
        return new Promise((resolve, reject) => {
            const url = Routing.generate('create_single_chat');
            $.ajax({
                url,
                method: 'POST',
                data: data,
            }).then((data, textStatus, jqXHR) => {
                debugger;
                resolve(data);
            }).catch((jqXHR) => {
                debugger;
                const errorData = JSON.parse(jqXHR.responseText);
                errorData.httpCode = jqXHR.status;
                reject(errorData);
            });
        });
    }

    _messageChat(data) {
        debugger;
        return new Promise((resolve, reject) => {
            const url = Routing.generate('message_chat', {'id': this.chat.id});
            $.ajax({
                url,
                method: 'POST',
                data: data,
            }).then((data, textStatus, jqXHR) => {
                debugger;
                resolve(data);
            }).catch((jqXHR) => {
                debugger;
                const errorData = JSON.parse(jqXHR.responseText);
                errorData.httpCode = jqXHR.status;
                reject(errorData);
            });
        });
    }

    render(message) {
        let html = messageTemplate(message);
        this.$wrapper.find(ChatWidget._selectors.messages).append(html);
    }
}

const messageTemplate = ({body, formattedSentDate, from: {fullName}}) => `
    <div><strong>${fullName}</strong><small>${formattedSentDate}</small></div>
    <div>${body}</div>
`;

export default ChatWidget;