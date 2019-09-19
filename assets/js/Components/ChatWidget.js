'use strict';

import $ from 'jquery';
import List from "list.js";
import Routing from "../Routing";
import Pusher from 'pusher-js';

class ChatWidget {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     * @param loggedInUserId
     */
    constructor($wrapper, globalEventDispatcher, loggedInUserId) {

        debugger;

        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.chat = {};
        this.loggedInUserId = loggedInUserId;
        this.userIdToMessage = null;

        this.unbindEvents();
        this.bindEvents();
    }

    unbindEvents() {
        this.$wrapper.off('click', ChatWidget._selectors.createChatButton);
        this.$wrapper.off('click', ChatWidget._selectors.sendMessageButton);
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
        this.userIdToMessage = data.userId = $(e.target).attr('data-userId');

        this._createOrGetChat(data).then((data) => {
            debugger;
           this.chat = data.data;

           for(let message of this.chat.messages) {
               this.render(message);
           }

           this._startListening();
        });
    }

   _startListening() {

        debugger;
       Pusher.logToConsole = true;

       const socket = new Pusher('3b8e318e4abe6c429446', {
           cluster: 'us2',
           forceTLS: true
       });

       let channel = socket.subscribe('chat-' + this.loggedInUserId);

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
        this._messageChat(data).then((data) => {

            // we need to refresh the messages after you've sent this
            // There are plenty of dif ways to do this. This requires another api call
            // which def isn't necessary as you can just use JS to append the most recently sent message
            // once the call returns success

            data = {};
            data.userId = this.userIdToMessage;

            this._createOrGetChat(data).then((data) => {
                debugger;
                this.chat = data.data;
                this._renderMessages(this.chat.messages);

                //this._startListening();
            });
        });
    }

    _renderMessages(messages) {

        this.$wrapper.find(ChatWidget._selectors.messages).html("");

        for(let message of messages) {
            this.render(message);
        }
    }

    _createOrGetChat(data) {
        return new Promise((resolve, reject) => {
            const url = Routing.generate('create_or_get_chat');
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

const messageTemplate = ({body, formattedSentDate, sentFrom: {fullName}}) => `
    <div><strong>${fullName}</strong><small>${formattedSentDate}</small></div>
    <div>${body}</div>
`;

export default ChatWidget;