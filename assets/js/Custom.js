import Quill from 'quill';
import $ from "jquery";

jQuery(document).ready(function($) {

    var youtubeAPIKey = 'AIzaSyDRsAB-EVUDoPlO2Aq4QdB5fGlFrICJqbw';

    /**
     * Smooth Page Transitions
     */
    $('body').addClass('ready');

    /**
     * WYSIWYG
     */
    const toolbarOptions = [
        ['bold', 'italic', 'underline', 'strike'],        // toggled buttons
        // ['blockquote', 'code-block'],

        [{ 'header': 1 }, { 'header': 2 }],               // custom button values
        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
        // [{ 'script': 'sub'}, { 'script': 'super' }],      // superscript/subscript
        [{ 'indent': '-1'}, { 'indent': '+1' }],          // outdent/indent
        [{ 'direction': 'ltr' }],                         // text direction

        // [{ 'size': ['small', false, 'large', 'huge'] }],  // custom dropdown
        [{ 'header': [1, 2, 3, 4, 5, 6, false] }],

        // [{ 'color': [] }, { 'background': [] }],          // dropdown with defaults from theme
        // [{ 'font': [] }],
        // [{ 'align': [] }],

        ['clean'],                                         // remove formatting button
        ['link'],
        // ['link', 'image', 'video'],
        ['showHtml']
    ];

    let instance = 1;
    $('[data-wysiwyg]').each(function(){
        (function($elem) {

            // Get the Target
            const targetSelector = $elem.attr('data-wysiwyg');
            const $target = $(targetSelector);

            // Add a unique instance class
            const uniqueClass = 'wysiwyg-editor-' + instance;
            $elem.addClass(uniqueClass);

            // Instantiate the Editor
            const quill = new Quill(`.${uniqueClass}`, {
                modules: {
                    toolbar: toolbarOptions
                },
                placeholder: $elem.attr('data-placeholder'),
                theme: 'snow'
            });

            // HTML editor - Create Virtual Source Textarea
            const htmlSrcTextarea = document.createElement('textarea');
            htmlSrcTextarea.style.cssText = "width: 100%;margin: 0px;background: rgb(29, 29, 29);box-sizing: border-box;color: rgb(204, 204, 204);font-size: 15px;outline: none;padding: 20px;line-height: 24px;font-family: Consolas, Menlo, Monaco, &quot;Courier New&quot;, monospace;position: absolute;top: 0;bottom: 0;border: none;display:none";

            // HTML editor - Toolbar
            const htmlEditor = quill.addContainer('ql-custom');
            htmlEditor.appendChild(htmlSrcTextarea);

            const $htmlEditor = $(`.${uniqueClass}`);
            const $htmlEditorWrapper = $htmlEditor.parent();

            $htmlEditorWrapper.on('click', '.ql-showHtml', function() {
                if (htmlSrcTextarea.style.display === '') {
                    quill.pasteHTML(htmlSrcTextarea.value)
                }
                htmlSrcTextarea.style.display = htmlSrcTextarea.style.display === 'none' ? '' : 'none'
            });

            // Populate the editor with any content that already exists from target
            quill.root.innerHTML = $target.val();

            // Listen for events and update the target
            quill.on('text-change', function(delta, oldDelta, source) {
                $target.val(
                    quill.root.innerHTML
                );

                htmlSrcTextarea.value = quill.root.innerHTML;
            });

            // Increment the unique instance
            instance++;

        })($(this));
    });

    /**
     * Auto Upload Files in Forms
     */
    instance = 1;
    $('[data-upload-url]').each(function(){
        (function($elem) {

            // Valid options so far
            // image:#whereToAppendElement
            // multiple:image:#whereToAppendElement (needs a data-template as well)

            // Get the Target
            const url = $elem.attr('data-upload-url');
            const dataType = $elem.attr('data-type');
            const type = dataType.split(':')[0];
            const _template = $elem.html();
            $elem.empty();

            // Add a unique instance class
           $elem.addClass('js-file-ajax-upload-' + instance);

            // Append the necessary elements
            if(type === "multiple") {
                $elem.append('<div class="js-upload-' + instance +' uk-placeholder uk-text-center">\n' +
                    '    <span uk-icon="icon: cloud-upload"></span>\n' +
                    '    <span class="uk-text-middle">Attach files by dropping them here or</span>\n' +
                    '    <div data-uk-form-custom>\n' +
                    '        <input type="file" multiple>\n' +
                    '        <span class="uk-link">selecting one</span>\n' +
                    '    </div>\n' +
                    '</div>');
            } else {
                $elem.append('<div class="js-upload-' + instance +'" data-uk-form-custom>\n' +
                    '    <input type="file" multiple>\n' +
                    '    <button class="uk-button uk-button-default" type="button" tabindex="-1">Upload</button>\n' +
                    '</div>');
            }

            const $progressBar = $('<progress id="js-progressbar" class="uk-progress" value="0" max="100" style="display: none;"></progress>');
            $elem.append($progressBar);

            // Instantiate the Upload Field
            UIkit.upload(`.js-upload-${instance}`, {

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
                complete: function () {

                    // console.log('complete', arguments);
                    const response = JSON.parse(arguments[0].response);

                    if( response && response.success === true ) {
                        switch(type) {
                            case 'multiple':
                                var uploadType = dataType.split(':')[1];
                                if( uploadType === "image" ) {
                                    $(`#${dataType.split(':')[2]}`).append(
                                        _template.replace(/UPLOAD_ID/g, response.id).replace(/UPLOAD_URL/g, response.url)
                                    );
                                }
                                break;
                            case 'image':
                                $(`#${dataType.split(':')[1]}`).attr("src", response.url);
                                break;
                        }

                        window.Pintex.notification("Uploaded file successfully", "success");

                    } else {
                        window.Pintex.notification("Failed to upload file", "error");
                    }
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


            // Increment the unique instance
            instance++;

        })($(this));
    });

    /**
     * Errors Triggering Correct Tabs
     */
    $('.uk-switcher:not(".uk-switcher .uk-switcher")').each(function() {

        // Find Index of First Tab With Error
        var $tab = $(this).children().has('ul.errors').first();
        if( $tab.length > 0 ) {
            var index = $tab.index();
            $(this).children().removeClass('uk-active').eq(index).addClass('uk-active');
            $("[uk-switcher*=" + $(this).attr('id') + "]").children().removeClass('uk-active').eq(index).addClass('uk-active');

            // Find First Inner Tab With Error (if applicable)
            var $switcher = $tab.find('.uk-switcher');
            var $innerTab = $switcher.children().has('ul.errors').first();
            if( $innerTab.length > 0 ) {
                var innerIndex = $innerTab.index();
                $switcher.children().removeClass('uk-active').eq(innerIndex).addClass('uk-active');
                $("[uk-tab*=" + $switcher.attr('id') + "]").children().removeClass('uk-active').eq(innerIndex).addClass('uk-active');
            }
        }
    });

    /**
     * AJAX Delete
     */
    $(document).on('click', '[data-remove]', function() {

        const $elem = $(this);

        $.get( $elem.attr('data-remove') ).always(function( response ) {
            if ( response.success === true ) {
                window.Pintex.notification("Successfully deleted.", "success");
                $elem.parent().remove();
            } else {
                window.Pintex.notification("Unable to delete. Refresh the page and try again.", "warning");
            }
        });
    });

    /**
     * Select All
     */
    $(document).on('click', '[data-select-all]', function() {
       const nameOfTargets = $(this).attr('data-select-all');
       $('[name="'+nameOfTargets+'"]').prop( "checked", true );
    });

    /**
     * DeSelect All
     */
    $(document).on('click', '[data-deselect-all]', function() {
        const nameOfTargets = $(this).attr('data-deselect-all');
        $('[name="'+nameOfTargets+'"]').prop( "checked", false );
    });

    /**
     * Form Modals
     */
    $(document).on('click', '[data-modal-form]', function(e) {
        e.preventDefault();
        const sourceHTML = $( $(this).attr('data-modal-form') ).html();
        window.Pintex.modal.dynamic_open( sourceHTML );
    });

    /**
     * Company Resource Forms
     */
    $(document).on('click', '#modal-add-company-video [data-action]', function(e) {
        e.preventDefault();

        const url = $(this).attr('data-action');
        const $modalBody = $(this).closest('.uk-modal-body');
        const $fields = $modalBody.find('[name]');
        const $nameField = $modalBody.find('[name="name"]');
        const name = $nameField.val();
        const $videoField = $modalBody.find('[name="videoId"]');
        const videoId = youtube_parser( $videoField.val() ) || $videoField.val();

        // Smart Set
        $videoField.val( videoId );

        $videoField.removeClass('uk-form-success uk-form-error');

        // Validate Youtube Video ID
        $.ajax( `https://www.googleapis.com/youtube/v3/videos?part=id&id=${videoId}&key=${youtubeAPIKey}` ).always(function( response ) {
            if( response && response.etag ) {
                // Turn the youtube Video Field Green/Red Depending
                if( response.items.length ) {
                    $videoField.addClass('uk-form-success');
                    $.ajax({
                        url: url,
                        data: {
                          name: name,
                          videoId: videoId
                        },
                        method: "POST",
                        complete: function(serverResponse) {

                            const response = serverResponse.responseJSON;

                            if( response.success ) {
                                let _template = $('#companyVideosTemplate').html();
                                $fields.val('').removeClass('uk-form-success uk-form-danger');
                                $('#companyVideos').append(
                                    _template.replace(/RESOURCE_ID/g, response.id).replace(/VIDEO_ID/g, response.videoId).replace(/VIDEO_NAME/g, response.name)
                                );
                                UIkit.modal( '#modal-add-company-video' ).hide();
                                window.Pintex.notification("Video uploaded.", "success");
                            } else {
                                window.Pintex.notification("Unable to upload video. Please try again.", "danger");
                            }
                        }
                    });
                } else {
                    $videoField.addClass('uk-form-danger');
                    window.Pintex.notification("Enter a valid Youtube Video ID.", "danger");
                }
            } else {
                window.Pintex.notification("Something went wrong. Please try again later.", "danger");
            }
        });

    });

    $(document).on('click', '#modal-add-company-resource [data-action]', function(e) {
        e.preventDefault();

        const url = $(this).attr('data-action');
        const $modalBody = $(this).closest('.uk-modal-body');
        const $fields = $modalBody.find('[name]');
        const $titleField = $modalBody.find('[name="title"]');
        const $descriptionField = $modalBody.find('[name="description"]');
        const $fileField = $modalBody.find('[name="resource"]');

        var formData = new FormData();
        formData.append('title', $titleField.val() );
        formData.append('description', $descriptionField.val() );
        formData.append('resource', $fileField[0].files[0]);

        $.ajax({
            url: url,
            data: formData,
            contentType: false,
            processData: false,
            type: "POST",
            complete: function (serverResponse) {

                const response = serverResponse.responseJSON;

                if (response.success) {
                    let _template = $('#companyResourcesTemplate').html();
                    $fields.val('');
                    $('#companyResources').append(
                        _template.replace(/RESOURCE_ID/g, response.id).replace(/RESOURCE_TITLE/g, response.title).replace(/RESOURCE_DESCRIPTION/g, response.description).replace(/RESOURCE_URL/g, response.url)
                    );
                    UIkit.modal('#modal-add-company-resource').hide();
                    window.Pintex.notification("Resource uploaded.", "success");
                } else {
                    window.Pintex.notification("Unable to upload resource. Please try again.", "danger");
                }
            }
        });

    });

    $(document).on('click', '#modal-add-company-event-resource [data-action]', function(e) {
        e.preventDefault();

        const url = $(this).attr('data-action');
        const $modalBody = $(this).closest('.uk-modal-body');
        const $fields = $modalBody.find('[name]');
        const $titleField = $modalBody.find('[name="title"]');
        const $descriptionField = $modalBody.find('[name="description"]');
        const $fileField = $modalBody.find('[name="resource"]');

        var formData = new FormData();
        formData.append('title', $titleField.val() );
        formData.append('description', $descriptionField.val() );
        formData.append('resource', $fileField[0].files[0]);

        $.ajax({
            url: url,
            data: formData,
            contentType: false,
            processData: false,
            type: "POST",
            complete: function (serverResponse) {

                const response = serverResponse.responseJSON;

                if (response.success) {
                    let _template = $('#companyEventResourcesTemplate').html();
                    $fields.val('');
                    $('#companyEventResources').append(
                        _template.replace(/RESOURCE_ID/g, response.id).replace(/RESOURCE_TITLE/g, response.title).replace(/RESOURCE_DESCRIPTION/g, response.description).replace(/RESOURCE_URL/g, response.url)
                    );
                    UIkit.modal('#modal-add-company-event-resource').hide();
                    window.Pintex.notification("Resource uploaded.", "success");
                } else {
                    window.Pintex.notification("Unable to upload resource. Please try again.", "danger");
                }
            }
        });

    });

    $(document).on('click', '#modal-add-lesson-resource [data-action]', function(e) {
        e.preventDefault();

        const url = $(this).attr('data-action');
        const $modalBody = $(this).closest('.uk-modal-body');
        const $fields = $modalBody.find('[name]');
        const $titleField = $modalBody.find('[name="title"]');
        const $descriptionField = $modalBody.find('[name="description"]');
        const $fileField = $modalBody.find('[name="resource"]');

        var formData = new FormData();
        formData.append('title', $titleField.val() );
        formData.append('description', $descriptionField.val() );
        formData.append('resource', $fileField[0].files[0]);

        $.ajax({
            url: url,
            data: formData,
            contentType: false,
            processData: false,
            type: "POST",
            complete: function (serverResponse) {

                const response = serverResponse.responseJSON;

                if (response.success) {
                    let _template = $('#lessonResourcesTemplate').html();
                    $fields.val('');
                    $('#lessonResources').append(
                        _template.replace(/RESOURCE_ID/g, response.id).replace(/RESOURCE_TITLE/g, response.title).replace(/RESOURCE_DESCRIPTION/g, response.description).replace(/RESOURCE_URL/g, response.url)
                    );
                    UIkit.modal('#modal-add-lesson-resource').hide();
                    window.Pintex.notification("Resource uploaded.", "success");
                } else {
                    window.Pintex.notification("Unable to upload resource. Please try again.", "danger");
                }
            }
        });

    });

    /**
     * School Resource Forms
     */
    $(document).on('click', '#modal-add-school-video [data-action]', function(e) {
        e.preventDefault();

        const url = $(this).attr('data-action');
        const $modalBody = $(this).closest('.uk-modal-body');
        const $fields = $modalBody.find('[name]');
        const $nameField = $modalBody.find('[name="name"]');
        const name = $nameField.val();
        const $videoField = $modalBody.find('[name="videoId"]');
        const videoId = youtube_parser( $videoField.val() ) || $videoField.val();

        // Smart Set
        $videoField.val( videoId );

        $videoField.removeClass('uk-form-success uk-form-error');

        // Validate Youtube Video ID
        $.ajax( `https://www.googleapis.com/youtube/v3/videos?part=id&id=${videoId}&key=${youtubeAPIKey}` ).always(function( response ) {
            if( response && response.etag ) {
                // Turn the youtube Video Field Green/Red Depending
                if( response.items.length ) {
                    $videoField.addClass('uk-form-success');
                    $.ajax({
                        url: url,
                        data: {
                            name: name,
                            videoId: videoId
                        },
                        method: "POST",
                        complete: function(serverResponse) {

                            const response = serverResponse.responseJSON;

                            if( response.success ) {
                                let _template = $('#schoolVideosTemplate').html();
                                $fields.val('').removeClass('uk-form-success uk-form-danger');
                                $('#schoolVideos').append(
                                    _template.replace(/RESOURCE_ID/g, response.id).replace(/VIDEO_ID/g, response.videoId).replace(/VIDEO_NAME/g, response.name)
                                );
                                UIkit.modal( '#modal-add-school-video' ).hide();
                                window.Pintex.notification("Video uploaded.", "success");
                            } else {
                                window.Pintex.notification("Unable to upload video. Please try again.", "danger");
                            }
                        }
                    });
                } else {
                    $videoField.addClass('uk-form-danger');
                    window.Pintex.notification("Enter a valid Youtube Video ID.", "danger");
                }
            } else {
                window.Pintex.notification("Something went wrong. Please try again later.", "danger");
            }
        });

    });

    $(document).on('click', '#modal-add-school-resource [data-action]', function(e) {
        e.preventDefault();

        const url = $(this).attr('data-action');
        const $modalBody = $(this).closest('.uk-modal-body');
        const $fields = $modalBody.find('[name]');
        const $titleField = $modalBody.find('[name="title"]');
        const $descriptionField = $modalBody.find('[name="description"]');
        const $fileField = $modalBody.find('[name="resource"]');

        var formData = new FormData();
        formData.append('title', $titleField.val() );
        formData.append('description', $descriptionField.val() );
        formData.append('resource', $fileField[0].files[0]);

        $.ajax({
            url: url,
            data: formData,
            contentType: false,
            processData: false,
            type: "POST",
            complete: function (serverResponse) {

                const response = serverResponse.responseJSON;

                if (response.success) {
                    let _template = $('#schoolResourcesTemplate').html();
                    $fields.val('');
                    $('#schoolResources').append(
                        _template.replace(/RESOURCE_ID/g, response.id).replace(/RESOURCE_TITLE/g, response.title).replace(/RESOURCE_DESCRIPTION/g, response.description).replace(/RESOURCE_URL/g, response.url)
                    );
                    UIkit.modal('#modal-add-school-resource').hide();
                    window.Pintex.notification("Resource uploaded.", "success");
                } else {
                    window.Pintex.notification("Unable to upload resource. Please try again.", "danger");
                }
            }
        });

    });

    /**
     * Time Pickers
     */
    $('.uk-timepicker').each(function( index ) {
        var $elem = $(this);
        var dropDirection = $elem.hasClass('uk-timepicker-up') ? "up" : "down";
        console.log( dropDirection );

        $elem.daterangepicker({
            drops: dropDirection,
            singleDatePicker: true,
            timePicker: true,
            timePickerIncrement: 15,
            linkedCalendars: false,
            showCustomRangeLabel: false,
            locale: {
                format: 'MM/DD/YYYY h:mm A'
            }
        }, function(start, end, label) {
            console.log('New date range selected: ' + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD') + ' (predefined range: ' + label + ')');
        });

    });

    /**
     * Parse Youtube Urls to get Video IDs
     */
    function youtube_parser(url){
        var regExp = /^.*((youtu.be\/)|(v\/)|(\/u\/\w\/)|(embed\/)|(watch\?))\??v?=?([^#\&\?]*).*/;
        var match = url.match(regExp);
        return (match&&match[7].length==11)? match[7] : false;
    }

    /**
     * Live Chat Users from Link/Button
     */

    $(document).on("click", "[data-message-user-id]", function() {
        var userId = $(this).attr('data-message-user-id');
        var message = $(this).attr('data-message');

        if (navigator.userAgent.indexOf('MSIE') !== -1 || navigator.appVersion.indexOf('Trident/') > 0) {
            var customEvent = document.createEvent("CustomEvent");
            customEvent.initCustomEvent('live-chat-user', false, false,{
                "userId": userId,
                "message": message,
            });
            window.dispatchEvent(customEvent);
        } else {
            window.dispatchEvent(new CustomEvent("live-chat-user", { "detail": { "userId": userId, "message": message } } ))
        }
    })

    /**
     * Fire off a custom event to update all of the calendars on tab switching
     */
    $(document).on( "click", ".uk-tab", function() {
        setTimeout(function() {
            if (navigator.userAgent.indexOf('MSIE') !== -1 || navigator.appVersion.indexOf('Trident/') > 0) {
                var customEvent = document.createEvent("CustomEvent");
                customEvent.initCustomEvent('uk-tab-clicked', false, false,{});
                window.dispatchEvent(customEvent);
            } else {
                window.dispatchEvent(new CustomEvent("uk-tab-clicked", { "detail": {} } ))
            }
        }, 10);
    });

    /**
     * Stop videos when sidebar is closed
     */
    $('#offcanvas-help').on('hide.uk.offcanvas', function() {
        $('iframe').each(function() {
            $(this).attr('src', $(this).attr('src'));
        });
    });

});
