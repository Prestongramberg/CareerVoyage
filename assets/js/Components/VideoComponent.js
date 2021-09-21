'use strict';

import $ from 'jquery';
import List from "list.js";
import Routing from "../Routing";
import UIkit from "uikit";

class VideoComponent {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     */
    constructor($wrapper, globalEventDispatcher) {

        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;

        this.unbindEvents();
        this.bindEvents();
    }

    unbindEvents() {

        this.$wrapper.off('click', VideoComponent._selectors.addVideoButton);
        this.$wrapper.off('click', VideoComponent._selectors.editVideoButton);
        this.$wrapper.off('click', VideoComponent._selectors.deleteVideoButton);
        $(document).off('click', VideoComponent._selectors.addVideoFormSubmitButton);
        $(document).off('click', VideoComponent._selectors.editVideoFormSubmitButton);
    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            addVideoButton: '.js-add-video-button',
            editVideoButton: '.js-edit-video-button',
            deleteVideoButton: '.js-delete-video-button',
            addVideoFormSubmitButton: '.js-add-video-form-submit-button',
            editVideoFormSubmitButton: '.js-edit-video-form-submit-button',
            videoListContainer: '.js-video-list-container'
        }
    }

    bindEvents() {

        this.$wrapper.on('click', VideoComponent._selectors.addVideoButton, this.handleAddVideoButtonClick.bind(this));
        this.$wrapper.on('click', VideoComponent._selectors.editVideoButton, this.handleEditVideoButtonClick.bind(this));
        this.$wrapper.on('click', VideoComponent._selectors.deleteVideoButton, this.handleDeleteVideoButtonClick.bind(this));
        $(document).on('click', VideoComponent._selectors.addVideoFormSubmitButton, this.handleAddVideoFormSubmit.bind(this));
        $(document).on('click', VideoComponent._selectors.editVideoFormSubmitButton, this.handleEditVideoFormSubmit.bind(this));
    }

    handleAddVideoButtonClick(event) {

        if (event.cancelable) {
            event.preventDefault();
        }

        let url = $(event.currentTarget).attr('data-url');

        $.ajax({
            url: url,
            method: 'GET'
        }).then((data, textStatus, jqXHR) => {

        }).catch((jqXHR) => {
            const errorData = JSON.parse(jqXHR.responseText);

            UIkit.modal('#js-video-component-modal').show();

            $('#js-video-component-modal').find('.uk-modal-body').html(errorData.formMarkup);
        });
    }

    handleEditVideoButtonClick(event) {

        if (event.cancelable) {
            event.preventDefault();
        }

        let url = $(event.currentTarget).attr('data-url');

        $.ajax({
            url: url,
            method: 'GET'
        }).then((data, textStatus, jqXHR) => {

        }).catch((jqXHR) => {
            const errorData = JSON.parse(jqXHR.responseText);

            UIkit.modal('#js-video-component-modal').show();

            $('#js-video-component-modal').find('.uk-modal-body').html(errorData.formMarkup);
        });
    }

    handleDeleteVideoButtonClick(event) {

        if (event.cancelable) {
            event.preventDefault();
        }

        let url = $(event.currentTarget).attr('data-url');

        $.ajax({
            url: url,
            method: 'GET'
        }).then((data, textStatus, jqXHR) => {

            $(VideoComponent._selectors.videoListContainer).find('#video-' + data.id).remove();

        }).catch((jqXHR) => {
            const errorData = JSON.parse(jqXHR.responseText);
        });
    }

    handleAddVideoFormSubmit(e) {

        if (e.cancelable) {
            e.preventDefault();
        }

        let $addVideoForm = $('.js-add-video-form');
        let url = $addVideoForm.attr('action');
        let formData = new FormData($addVideoForm.get(0));

        return new Promise((resolve, reject) => {
            $.ajax({
                url: url,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false
            }).then((data, textStatus, jqXHR) => {

                const html = videoTemplate(data.id, data.videoId, data.name, data.editUrl, data.deleteUrl);
                $(VideoComponent._selectors.videoListContainer).append($($.parseHTML(html)));
                UIkit.modal('#js-video-component-modal').hide();

                window.Pintex.notification("Video successfully added", "success");

            }).catch((jqXHR) => {
                const errorData = JSON.parse(jqXHR.responseText);

                $('#js-video-component-modal').find('.uk-modal-body').html(errorData.formMarkup);
            });
        });
    }

    handleEditVideoFormSubmit(e) {

        if (e.cancelable) {
            e.preventDefault();
        }

        let $editVideoForm = $('.js-edit-video-form');
        let url = $editVideoForm.attr('action');
        let formData = new FormData($editVideoForm.get(0));

        return new Promise((resolve, reject) => {
            $.ajax({
                url: url,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false
            }).then((data, textStatus, jqXHR) => {

                const html = videoTemplate(data.id, data.videoId, data.name, data.editUrl, data.deleteUrl);

                $(VideoComponent._selectors.videoListContainer).find('#video-' + data.id).replaceWith($($.parseHTML(html)));

                UIkit.modal('#js-video-component-modal').hide();

                window.Pintex.notification("Video successfully updated", "success");

            }).catch((jqXHR) => {
                const errorData = JSON.parse(jqXHR.responseText);

                $('#js-video-component-modal').find('.uk-modal-body').html(errorData.formMarkup);
            });
        });
    }
}

const videoTemplate = (id, videoId, name, editUrl, deleteUrl) => `
            <div id="video-${id}" class="video">
                <a class="uk-inline" href="https://www.youtube.com/watch?v=${videoId}">
                    <img src="http://i.ytimg.com/vi/${videoId}/hqdefault.jpg" alt="">
                    <div class="company-video__overlay">
                        <div class="company-video__overlay-title">
                            ${name}
                        </div>
                    </div>
                </a>
                
                <button data-url="${editUrl}" style="position: absolute; top: 3px; left: 43px; z-index: 5000; border: none; color: #999;"
                  class="js-edit-video-button" type="button" uk-icon="icon: file-edit"></button>
                                      
              <button data-url="${deleteUrl}" class="js-delete-video-button" type="button" uk-close></button>
                
           </div>
    
        `;

export default VideoComponent;