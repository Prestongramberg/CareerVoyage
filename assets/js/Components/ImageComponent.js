'use strict';

import $ from 'jquery';
import List from "list.js";
import Routing from "../Routing";
import UIkit from "uikit";

class ImageComponent {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     */
    constructor($wrapper, globalEventDispatcher) {

        debugger;

        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;

        this.render();
        this.unbindEvents();
        this.bindEvents();
    }

    render() {

        debugger;

        const url = this.$wrapper.attr('data-url');
        const type = this.$wrapper.attr('data-type');

       /* const hiddenField = $elem.attr('data-hidden-field') || null;
        const type = dataType.split(':')[0];
        const _template = $elem.html();
        $elem.empty();*/

        // Add a unique instance class
        //$elem.addClass('js-file-ajax-upload-' + instance);

        // Append the necessary elements
        if (type === "multiple") {
            $(ImageComponent._selectors.imageUploadContainer).append('<div class="js-upload uk-placeholder uk-text-center">\n' +
                '    <span uk-icon="icon: cloud-upload"></span>\n' +
                '    <span class="uk-text-middle">Attach files by dropping them here or</span>\n' +
                '    <div data-uk-form-custom>\n' +
                '        <input type="file" multiple>\n' +
                '        <span class="uk-link">selecting one</span>\n' +
                '    </div>\n' +
                '</div>');
        } else {
            $(ImageComponent._selectors.imageUploadContainer).append('<div class="js-upload" data-uk-form-custom>\n' +
                '    <input type="file" multiple>\n' +
                '    <button class="uk-button uk-button-default" type="button" tabindex="-1">Upload</button>\n' +
                '</div>');
        }

        const $progressBar = $('<progress id="js-progressbar" class="uk-progress" value="0" max="100" style="display: none;"></progress>');
        $(ImageComponent._selectors.imageUploadContainer).append($progressBar);

        // Instantiate the Upload Field
        UIkit.upload('.js-upload', {

            url: url,
            multiple: type === "multiple",
            name: 'file',

            beforeSend: function (environment) {
                // console.log('beforeSend', arguments);
            },
            beforeAll: function () {
                // console.log('beforeAll', arguments);
            },
            load: function () {
                // console.log('load', arguments);
            },
            error: function () {
                console.log('error', arguments);
                window.Pintex.notification("Failed to upload file", "error");
            },
            complete: (response) => {

                debugger;
                response = JSON.parse(response.response);

                const html = imageTemplate(response.id, response.unCroppedUrl, response.url, response.deleteUrl);

                $(ImageComponent._selectors.imageListContainer).append($($.parseHTML(html)));

                window.Pintex.notification("Uploaded file successfully", "success");
            },

            loadStart: function (e) {
                // console.log('loadStart', arguments);
                $progressBar.fadeIn();
                $progressBar.attr({
                    'max': e.total,
                    'value': e.loaded
                });
            },

            progress: function (e) {
                // console.log('progress', arguments);
                $progressBar.attr({
                    'max': e.total,
                    'value': e.loaded
                });
            },

            loadEnd: function (e) {
                // console.log('loadEnd', arguments);
                $progressBar.attr({
                    'max': e.total,
                    'value': e.loaded
                });
            },

            completeAll: function () {
                // console.log('completeAll', arguments);
                setTimeout(function () {
                    $progressBar.fadeOut();
                }, 1000);
            }

        });

    }

    unbindEvents() {

        /*this.$wrapper.off('click', ImageComponent._selectors.addVideoButton);
        this.$wrapper.off('click', ImageComponent._selectors.editVideoButton);
        this.$wrapper.off('click', ImageComponent._selectors.deleteVideoButton);
        $(document).off('click', ImageComponent._selectors.addVideoFormSubmitButton);
        $(document).off('click', ImageComponent._selectors.editVideoFormSubmitButton);*/
    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            deleteImageButton: '.js-delete-image-button',
            imageListContainer: '.js-image-list-container',
            imageUploadContainer: '.js-image-upload-container'
        }
    }

    bindEvents() {
        this.$wrapper.on('click', ImageComponent._selectors.deleteImageButton, this.handleDeleteImageButtonClick.bind(this));
    }

    handleDeleteImageButtonClick(event) {

        if (event.cancelable) {
            event.preventDefault();
        }

        let url = $(event.currentTarget).attr('data-url');

        $.ajax({
            url: url,
            method: 'GET'
        }).then((data, textStatus, jqXHR) => {

            $(ImageComponent._selectors.imageListContainer).find('#image-' + data.id).remove();

        }).catch((jqXHR) => {
            const errorData = JSON.parse(jqXHR.responseText);
        });
    }
}

const imageTemplate = (id, unCroppedUrl, url, deleteUrl) => `
        <div id="image-${id}" class="image">
            <a class="uk-inline" href="${unCroppedUrl}">
                <img src="${url}"
                     alt="">
                <div class="image__overlay">
                    <div class="image__overlay-title">
                        <span uk-icon="icon: expand"></span>
                    </div>
                </div>
            </a>
        
            <button data-url="${deleteUrl}" class="js-delete-image-button" type="button" uk-close></button>
        </div>
        `;

export default ImageComponent;