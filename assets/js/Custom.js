import Quill from 'quill';
import $ from "jquery";
import UIkit from 'uikit';
import Routing from "./Routing";

jQuery(document).ready(function ($) {
    const moment = require('moment-timezone');

    var today = moment().format('MM/DD/YYYY');
    var tomorrow = moment().add(1, 'days').format('MM/DD/YYYY');

    var youtubeAPIKey = 'AIzaSyCSkK3-PU4VSTu6VfmAxAf76o53zY4Fe9A';

    /**
     * Smooth Page Transitions
     */
    $('body').addClass('ready');

    $('#js-school-administrator-schools').on('change', function () {
        var url = $(this).val(); // get selected value
        if (url) { // require a URL
            window.location = url; // redirect
        }
        return false;
    });

    /**
     * WYSIWYG
     */
    const toolbarOptions = [
        ['bold', 'italic', 'underline', 'strike'],        // toggled buttons
        // ['blockquote', 'code-block'],

        [{'header': 1}, {'header': 2}],               // custom button values
        [{'list': 'ordered'}, {'list': 'bullet'}],
        // [{ 'script': 'sub'}, { 'script': 'super' }],      // superscript/subscript
        [{'indent': '-1'}, {'indent': '+1'}],          // outdent/indent
        [{'direction': 'ltr'}],                         // text direction

        // [{ 'size': ['small', false, 'large', 'huge'] }],  // custom dropdown
        [{'header': [1, 2, 3, 4, 5, 6, false]}],

        // [{ 'color': [] }, { 'background': [] }],          // dropdown with defaults from theme
        // [{ 'font': [] }],
        // [{ 'align': [] }],

        ['clean'],                                         // remove formatting button
        ['link'],
        // ['link', 'image', 'video'],
        ['showHtml']
    ];

    let instance = 1;
    $('[data-wysiwyg]').each(function () {
        (function ($elem) {

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

            $htmlEditorWrapper.on('click', '.ql-showHtml', function () {
                if (htmlSrcTextarea.style.display === '') {
                    quill.pasteHTML(htmlSrcTextarea.value)
                }
                htmlSrcTextarea.style.display = htmlSrcTextarea.style.display === 'none' ? '' : 'none'
            });

            // Populate the editor with any content that already exists from target
            quill.root.innerHTML = $target.val();

            // Listen for events and update the target
            quill.on('text-change', function (delta, oldDelta, source) {
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
    $('[data-upload-url]').each(function () {
        (function ($elem) {

            // Valid options so far
            // image:#whereToAppendElement
            // multiple:image:#whereToAppendElement (needs a data-template as well)

            // Get the Target
            const url = $elem.attr('data-upload-url');
            const dataType = $elem.attr('data-type');
            const hiddenField = $elem.attr('data-hidden-field') || null;
            const type = dataType.split(':')[0];
            const _template = $elem.html();
            $elem.empty();

            // Add a unique instance class
            $elem.addClass('js-file-ajax-upload-' + instance);

            // Append the necessary elements
            if (type === "multiple") {
                $elem.append('<div class="js-upload-' + instance + ' uk-placeholder uk-text-center">\n' +
                    '    <span uk-icon="icon: cloud-upload"></span>\n' +
                    '    <span class="uk-text-middle">Attach files by dropping them here or</span>\n' +
                    '    <div data-uk-form-custom>\n' +
                    '        <input type="file" multiple>\n' +
                    '        <span class="uk-link">selecting one</span>\n' +
                    '    </div>\n' +
                    '</div>');
            } else {
                $elem.append('<div class="js-upload-' + instance + '" data-uk-form-custom>\n' +
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
                complete: (response) => {

                    debugger;
                    // console.log('complete', arguments);
                    response = JSON.parse(response.response);

                    if (response && response.success === true) {
                        switch (type) {
                            case 'multiple':
                                var uploadType = dataType.split(':')[1];
                                if (uploadType === "image") {
                                    $(`#${dataType.split(':')[2]}`).append(
                                        _template.replace(/UPLOAD_ID/g, response.id).replace(/UPLOAD_URL/g, response.url)
                                    );
                                }
                                break;
                            case 'image':
                                $(`#${dataType.split(':')[1]}`).attr("src", response.url);

                                if(hiddenField) {
                                    $(`[name="${hiddenField}"]`).val(response.id);
                                }

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
    $('.uk-switcher:not(".uk-switcher .uk-switcher")').each(function () {

        // Find Index of First Tab With Error
        var $tab = $(this).children().has('ul.errors').first();
        if ($tab.length > 0) {
            var index = $tab.index();
            $(this).children().removeClass('uk-active').eq(index).addClass('uk-active');
            $("[uk-switcher*=" + $(this).attr('id') + "]").children().removeClass('uk-active').eq(index).addClass('uk-active');

            // Find First Inner Tab With Error (if applicable)
            var $switcher = $tab.find('.uk-switcher');
            var $innerTab = $switcher.children().has('ul.errors').first();
            if ($innerTab.length > 0) {
                var innerIndex = $innerTab.index();
                $switcher.children().removeClass('uk-active').eq(innerIndex).addClass('uk-active');
                $("[uk-tab*=" + $switcher.attr('id') + "]").children().removeClass('uk-active').eq(innerIndex).addClass('uk-active');
            }
        }
    });

    /**
     * AJAX Delete
     */
    $(document).on('click', '[data-remove]', function () {

        const $elem = $(this);

        $.get($elem.attr('data-remove')).always(function (response) {
            if (response.success === true) {
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
    $(document).on('click', '[data-select-all]', function () {
        const nameOfTargets = $(this).attr('data-select-all');
        $('[name="' + nameOfTargets + '"]').prop("checked", true);
    });

    /**
     * DeSelect All
     */
    $(document).on('click', '[data-deselect-all]', function () {
        const nameOfTargets = $(this).attr('data-deselect-all');
        $('[name="' + nameOfTargets + '"]').prop("checked", false);
    });

    /**
     * Form Modals
     */
    $(document).on('click', '[data-modal-form]', function (e) {
        e.preventDefault();
        const sourceHTML = $($(this).attr('data-modal-form')).html();
        window.Pintex.modal.dynamic_open(sourceHTML);
    });

    /**
     * Super Admin - Add Help Video
     */
    $(document).on('click', '#modal-add-help-video [data-action], #modal-edit-help-video [data-action]', function (e) {

        e.preventDefault();

        // debugger;
        const url = $(this).attr('data-action');
        const $modalBody = $(this).closest('.uk-modal-body');
        const $fields = $modalBody.find('[name]');
        const $nameField = $modalBody.find('[name="name"]');
        const name = $nameField.val();
        const $videoField = $modalBody.find('[name="videoId"]');
        const $userRoleField = $modalBody.find('[name="userRole"]');
        const userRole = $userRoleField.val();

        const videoId = youtube_parser($videoField.val()) || $videoField.val();

        // Smart Set
        $videoField.val(videoId);

        $videoField.removeClass('uk-form-success uk-form-error');

        // Validate Youtube Video ID
        $.ajax(`https://www.googleapis.com/youtube/v3/videos?part=id&id=${videoId}&key=${youtubeAPIKey}`).always(function (response) {
            if (response && response.etag) {
                // Turn the youtube Video Field Green/Red Depending
                if (response.items.length) {
                    $videoField.addClass('uk-form-success');
                    $.ajax({
                        url: url,
                        data: {
                            name: name,
                            videoId: videoId,
                            userRole: userRole
                        },
                        method: "POST",
                        complete: function (serverResponse) {

                            const response = serverResponse.responseJSON;

                            if (response.success) {
                                $fields.val('').removeClass('uk-form-success uk-form-danger');
                                window.Pintex.notification("Video uploaded. Reloading Video Results List...", "success");
                                // UIkit.modal( '#modal-add-help-video' ).hide();
                                // UIkit.modal( '#modal-edit-help-video' ).hide();

                                setTimeout(function () {
                                    window.location = Routing.generate('admin_videos');
                                }, 1000);
                            } else {
                                alert("Unable to upload video. Please try again.");
                            }
                        }
                    });
                } else {
                    $videoField.addClass('uk-form-danger');
                    alert("Please enter a valid Youtube Video ID.");
                }
            } else {
                alert("Something went wrong. Please try again later.");
            }
        });

    });

    /**
     * Educator Video Form
     */
    $(document).on('click', '#modal-add-educator-video [data-action]', function (e) {

        e.preventDefault();

        debugger;
        const url = $(this).attr('data-action');
        const $modalBody = $(this).closest('.uk-modal-body');
        const $fields = $modalBody.find('[name]');
        const $nameField = $modalBody.find('[name="name"]');
        const name = $nameField.val();
        const $videoField = $modalBody.find('[name="videoId"]');
        const $educatorField = $modalBody.find('[name="educatorId"]');
        const educatorId = $educatorField.val();

        const $tagsField = $modalBody.find('[name="tags"]');
        const tags = $tagsField.val();
        const videoId = youtube_parser($videoField.val()) || $videoField.val();

        // Smart Set
        $videoField.val(videoId);

        $videoField.removeClass('uk-form-success uk-form-error');

        const videoTemplate = ({id, videoId, name, tags}, videoRemoveUrl, videoEditUrl, educatorId) => `
            <div id="company-video-${id}" class="company-video">
                <a class="uk-inline" href="https://www.youtube.com/watch?v=${videoId}">
                    <img src="http://i.ytimg.com/vi/${videoId}/hqdefault.jpg" alt="">
                    <div class="company-video__overlay">
                        <div class="company-video__overlay-title">
                            ${name}
                        </div>
                    </div>
                </a>
                
              <a style="position: absolute; top: -6px; left: 10px; z-index: 5000; border: none; color: #999;"
               class="uk-button uk-button-default"
               href="#modal-edit-educator-video-${id}"
               uk-icon="icon: file-edit" uk-toggle></a>
               
                    <div class="modal-edit-educator-video" id="modal-edit-educator-video-${id}" uk-modal>
                        <div class="uk-modal-dialog uk-modal-body">
                            <h3>Edit Video</h3>

                            <input type="hidden" name="educatorId" value="${educatorId}">

                            <div class="uk-margin">
                                <label for="edit_company_edit_video_name" class="required">Name*</label>
                                <input type="text" id="edit_company_edit_video_name" name="name"
                                       required="required" class="uk-input"
                                       value="${name}"/>
                            </div>

                            <div class="uk-margin">
                                <label for="edit_company_edit_video_id" class="required">Youtube Video
                                    ID*</label>
                                <div class="uk-position-relative">
                                    <span class="uk-form-icon" uk-icon="icon: video-camera"></span>
                                    <input class="uk-input" id="edit_company_edit_video_id" name="videoId"
                                           required="required" type="text"
                                           value="${videoId}">
                                </div>
                                <small>Please make sure your video is open to public.</small>

                            </div>

                            <div class="uk-margin">
                                <label for="edit_company_edit_video_tags" class="required">Keywords</label>
                                <div class="uk-position-relative">
                            <textarea id="edit_company_edit_video_tags" name="tags"
                                      class="uk-textarea">${tags}</textarea>
                                </div>
                            </div>

                            <p class="uk-text-right">
                                <button class="uk-button uk-button-default uk-modal-close" type="button">
                                    Cancel
                                </button>
                                <input class="uk-button uk-button-primary" type="submit" value="Save" data-action="${videoEditUrl}"/>
                            </p>
                        </div>
                </div>
                                      
                <button type="button" data-remove="${videoRemoveUrl}" uk-close></button>
           </div>
    
        `;


        // Validate Youtube Video ID
        $.ajax(`https://www.googleapis.com/youtube/v3/videos?part=id&id=${videoId}&key=${youtubeAPIKey}`).always(function (response) {
            if (response && response.etag) {
                // Turn the youtube Video Field Green/Red Depending
                if (response.items.length) {
                    $videoField.addClass('uk-form-success');
                    $.ajax({
                        url: url,
                        data: {
                            name: name,
                            videoId: videoId,
                            tags: tags
                        },
                        method: "POST",
                        complete: function (serverResponse) {

                            const response = serverResponse.responseJSON;

                            if (response.success) {
                                $fields.val('').removeClass('uk-form-success uk-form-danger');
                                UIkit.modal('#modal-add-educator-video').hide();
                                window.Pintex.notification("Video uploaded.", "success");


                                let videoRemoveUrl = Routing.generate('educator_video_remove', {id: response.id});
                                let videoEditUrl = Routing.generate('educator_video_edit', {id: response.id});

                                const html = videoTemplate(response, videoRemoveUrl, videoEditUrl, educatorId);
                                const $video = $($.parseHTML(html));
                                $('#educatorVideos').append($video);

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

    /**
     * Educator Edit Video Form
     */
    $(document).on('click', '.modal-edit-educator-video [data-action]', function (e) {

        e.preventDefault();

        debugger;
        const url = $(this).attr('data-action');
        const $modalBody = $(this).closest('.uk-modal-body');
        const $fields = $modalBody.find('[name]');
        const $nameField = $modalBody.find('[name="name"]');
        const name = $nameField.val();
        const $videoField = $modalBody.find('[name="videoId"]');
        const $educatorField = $modalBody.find('[name="educatorId"]');
        const educatorId = $educatorField.val();

        const $tagsField = $modalBody.find('[name="tags"]');
        const tags = $tagsField.val();
        const videoId = youtube_parser($videoField.val()) || $videoField.val();

        // Smart Set
        $videoField.val(videoId);

        $videoField.removeClass('uk-form-success uk-form-error');

        const videoTemplate = ({id, videoId, name, tags}, videoRemoveUrl, videoEditUrl, educatorId) => `
            <div id="company-video-${id}" class="company-video">
                <a class="uk-inline" href="https://www.youtube.com/watch?v=${videoId}">
                    <img src="http://i.ytimg.com/vi/${videoId}/hqdefault.jpg" alt="">
                    <div class="company-video__overlay">
                        <div class="company-video__overlay-title">
                            ${name}
                        </div>
                    </div>
                </a>
                
              <a style="position: absolute; top: -6px; left: 10px; z-index: 5000; border: none; color: #999;"
               class="uk-button uk-button-default"
               href="#modal-edit-educator-video-${id}"
               uk-icon="icon: file-edit" uk-toggle></a>
               
                    <div class="modal-edit-educator-video" id="modal-edit-educator-video-${id}" uk-modal>
                        <div class="uk-modal-dialog uk-modal-body">
                            <h3>Edit Video</h3>

                            <input type="hidden" name="educatorId" value="${educatorId}">

                            <div class="uk-margin">
                                <label for="edit_company_edit_video_name" class="required">Name*</label>
                                <input type="text" id="edit_company_edit_video_name" name="name"
                                       required="required" class="uk-input"
                                       value="${name}"/>
                            </div>

                            <div class="uk-margin">
                                <label for="edit_company_edit_video_id" class="required">Youtube Video
                                    ID*</label>
                                <div class="uk-position-relative">
                                    <span class="uk-form-icon" uk-icon="icon: video-camera"></span>
                                    <input class="uk-input" id="edit_company_edit_video_id" name="videoId"
                                           required="required" type="text"
                                           value="${videoId}">
                                </div>
                                <small>Please make sure your video is open to public.</small>

                            </div>

                            <div class="uk-margin">
                                <label for="edit_company_edit_video_tags" class="required">Keywords</label>
                                <div class="uk-position-relative">
                            <textarea id="edit_company_edit_video_tags" name="tags"
                                      class="uk-textarea">${tags}</textarea>
                                </div>
                            </div>

                            <p class="uk-text-right">
                                <button class="uk-button uk-button-default uk-modal-close" type="button">
                                    Cancel
                                </button>
                                <input class="uk-button uk-button-primary" type="submit" value="Save" data-action="${videoEditUrl}"/>
                            </p>
                        </div>
                </div>
                                      
                <button type="button" data-remove="${videoRemoveUrl}" uk-close></button>
           </div>
    
        `;

        // Validate Youtube Video ID
        $.ajax(`https://www.googleapis.com/youtube/v3/videos?part=id&id=${videoId}&key=${youtubeAPIKey}`).always(function (response) {
            if (response && response.etag) {
                // Turn the youtube Video Field Green/Red Depending
                if (response.items.length) {
                    $videoField.addClass('uk-form-success');
                    $.ajax({
                        url: url,
                        data: {
                            name: name,
                            videoId: videoId,
                            tags: tags
                        },
                        method: "POST",
                        complete: function (serverResponse) {

                            const response = serverResponse.responseJSON;

                            if (response.success) {
                                $fields.val('').removeClass('uk-form-success uk-form-danger');
                                $(`#modal-edit-educator-video-${response.id}`).remove();
                                window.Pintex.notification("Video uploaded.", "success");

                                let videoRemoveUrl = Routing.generate('educator_video_remove', {id: response.id});
                                let videoEditUrl = Routing.generate('educator_video_edit', {id: response.id});

                                const html = videoTemplate(response, videoRemoveUrl, videoEditUrl, educatorId);
                                const $video = $($.parseHTML(html));

                                $(`#company-video-${response.id}`).replaceWith($video);



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

    /**
     * Edit General Career Video Form
     */
    $(document).on('click', '#modal-edit-career-video [data-action]', function (e) {

        e.preventDefault();

        debugger;
        const url = $(this).attr('data-action');
        const $modalBody = $(this).closest('.uk-modal-body');
        const $fields = $modalBody.find('[name]');
        const $nameField = $modalBody.find('[name="name"]');
        const name = $nameField.val();
        const $videoField = $modalBody.find('[name="videoId"]');
        debugger;
        const $secondaryIndustryFields = $modalBody.find('.secondaryIndustries');

        let secondaryIndustries = [];
        $secondaryIndustryFields.each(function (i, e) {
            secondaryIndustries.push($(this).val());
        });

        const $tagsField = $modalBody.find('[name="tags"]');
        const tags = $tagsField.val();
        const videoId = youtube_parser($videoField.val()) || $videoField.val();

        // Smart Set
        $videoField.val(videoId);

        $videoField.removeClass('uk-form-success uk-form-error');

        // Validate Youtube Video ID
        $.ajax(`https://www.googleapis.com/youtube/v3/videos?part=id&id=${videoId}&key=${youtubeAPIKey}`).always(function (response) {
            if (response && response.etag) {
                // Turn the youtube Video Field Green/Red Depending
                if (response.items.length) {
                    $videoField.addClass('uk-form-success');
                    $.ajax({
                        url: url,
                        data: {
                            secondaryIndustries: secondaryIndustries,
                            name: name,
                            videoId: videoId,
                            tags: tags
                        },
                        method: "POST",
                        complete: function (serverResponse) {

                            const response = serverResponse.responseJSON;

                            if (response.success) {
                                $fields.val('').removeClass('uk-form-success uk-form-danger');
                                UIkit.modal('#modal-add-career-video').hide();
                                window.Pintex.notification("Video updated. Reloading Video Results List...", "success");

                                setTimeout(function () {
                                    window.location = Routing.generate('video_index');
                                }, 1000);
                            } else {
                                window.Pintex.notification("Unable to edit video. Please try again.", "danger");
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


    /**
     * General Career Video Form
     */
    $(document).on('click', '#modal-add-career-video [data-action]', function (e) {

        e.preventDefault();

        debugger;
        const url = $(this).attr('data-action');
        const $modalBody = $(this).closest('.uk-modal-body');
        const $fields = $modalBody.find('[name]');
        const $nameField = $modalBody.find('[name="name"]');
        const name = $nameField.val();
        const $videoField = $modalBody.find('[name="videoId"]');
        debugger;
        const $secondaryIndustryFields = $modalBody.find('.secondaryIndustries');

        let secondaryIndustries = [];
        $secondaryIndustryFields.each(function (i, e) {
            secondaryIndustries.push($(this).val());
        });

        const $tagsField = $modalBody.find('[name="tags"]');
        const tags = $tagsField.val();
        const videoId = youtube_parser($videoField.val()) || $videoField.val();

        // Smart Set
        $videoField.val(videoId);

        $videoField.removeClass('uk-form-success uk-form-error');

        // Validate Youtube Video ID
        $.ajax(`https://www.googleapis.com/youtube/v3/videos?part=id&id=${videoId}&key=${youtubeAPIKey}`).always(function (response) {
            if (response && response.etag) {
                // Turn the youtube Video Field Green/Red Depending
                if (response.items.length) {
                    $videoField.addClass('uk-form-success');
                    $.ajax({
                        url: url,
                        data: {
                            secondaryIndustries: secondaryIndustries,
                            name: name,
                            videoId: videoId,
                            tags: tags
                        },
                        method: "POST",
                        complete: function (serverResponse) {

                            const response = serverResponse.responseJSON;

                            if (response.success) {
                                $fields.val('').removeClass('uk-form-success uk-form-danger');
                                UIkit.modal('#modal-add-career-video').hide();
                                window.Pintex.notification("Video uploaded. Reloading Video Results List...", "success");

                                setTimeout(function () {
                                    window.location = Routing.generate('video_index');
                                }, 1000);
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

    /**
     * Company Resource Forms
     */
    $(document).on('click', '#modal-add-company-video [data-action]', function (e) {
        e.preventDefault();

        const url = $(this).attr('data-action');
        const $modalBody = $(this).closest('.uk-modal-body');
        const $fields = $modalBody.find('[name]');
        const $nameField = $modalBody.find('[name="name"]');
        const name = $nameField.val();
        const $videoField = $modalBody.find('[name="videoId"]');
        const $tagsField = $modalBody.find('[name="tags"]');
        const tags = $tagsField.val();
        const videoId = youtube_parser($videoField.val()) || $videoField.val();

        // Smart Set
        $videoField.val(videoId);

        $videoField.removeClass('uk-form-success uk-form-error');

        // Validate Youtube Video ID
        $.ajax(`https://www.googleapis.com/youtube/v3/videos?part=id&id=${videoId}&key=${youtubeAPIKey}`).always(function (response) {
            if (response && response.etag) {
                // Turn the youtube Video Field Green/Red Depending
                if (response.items.length) {
                    $videoField.addClass('uk-form-success');
                    $.ajax({
                        url: url,
                        data: {
                            name: name,
                            videoId: videoId,
                            tags: tags
                        },
                        method: "POST",
                        complete: function (serverResponse) {

                            const response = serverResponse.responseJSON;

                            if (response.success) {
                                let _template = $('#companyVideosTemplate').html();
                                $fields.val('').removeClass('uk-form-success uk-form-danger');
                                $('#companyVideos').append(
                                    _template.replace(/RESOURCE_ID/g, response.id).replace(/VIDEO_ID/g, response.videoId).replace(/VIDEO_NAME/g, response.name)
                                );
                                UIkit.modal('#modal-add-company-video').hide();
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


    /**
     * School Resource Forms
     */
    $(document).on('click', '#modal-add-school-video [data-action]', function (e) {
        e.preventDefault();

        const url = $(this).attr('data-action');
        const $modalBody = $(this).closest('.uk-modal-body');
        const $fields = $modalBody.find('[name]');
        const $nameField = $modalBody.find('[name="name"]');
        const name = $nameField.val();
        const $videoField = $modalBody.find('[name="videoId"]');
        const videoId = youtube_parser($videoField.val()) || $videoField.val();

        // Smart Set
        $videoField.val(videoId);

        $videoField.removeClass('uk-form-success uk-form-error');

        // Validate Youtube Video ID
        $.ajax(`https://www.googleapis.com/youtube/v3/videos?part=id&id=${videoId}&key=${youtubeAPIKey}`).always(function (response) {
            if (response && response.etag) {
                // Turn the youtube Video Field Green/Red Depending
                if (response.items.length) {
                    $videoField.addClass('uk-form-success');
                    $.ajax({
                        url: url,
                        data: {
                            name: name,
                            videoId: videoId
                        },
                        method: "POST",
                        complete: function (serverResponse) {

                            const response = serverResponse.responseJSON;

                            if (response.success) {
                                let _template = $('#schoolVideosTemplate').html();
                                $fields.val('').removeClass('uk-form-success uk-form-danger');
                                $('#schoolVideos').append(
                                    _template.replace(/RESOURCE_ID/g, response.id).replace(/VIDEO_ID/g, response.videoId).replace(/VIDEO_NAME/g, response.name)
                                );
                                UIkit.modal('#modal-add-school-video').hide();
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


    /**
     * Time Pickers
     */
    $('.uk-timepicker').each(function (index) {
        var $elem = $(this);
        var dropDirection = $elem.hasClass('uk-timepicker-up') ? "up" : "down";

        if ($('.end-date-picker').length) {

            if ($elem.closest('form').hasClass('edit-form')) {
                today = $('.start-date-picker').val();
                tomorrow = $('.end-date-picker').val();
            }

            // for selecting a date range
            $elem.daterangepicker({
                "drops": dropDirection,
                "timePicker": true,
                "timePickerIncrement": 5,
                "startDate": today,
                "endDate": tomorrow,
                "autoApply": true,
                "locale": {
                    "format": 'MM/DD/YYYY h:mm A'
                }
            }, function (start, end, label) {
                $('.start-date-picker').val(start.format('MM/DD/YYYY h:mm A'));
                $('.end-date-picker').val(end.format('MM/DD/YYYY h:mm A'));
                console.log('New date range selected: ' + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD') + ' (predefined range: ' + label + ')');
            });

            // $elem.val( $('.start-date-picker').val() + " - " + $('.end-date-picker').val());

        } else {

            // For selecting a single date
            $elem.daterangepicker({
                drops: dropDirection,
                singleDatePicker: true,
                timePicker: true,
                timePickerIncrement: 5,
                linkedCalendars: false,
                showCustomRangeLabel: false,
                locale: {
                    format: 'MM/DD/YYYY h:mm A'
                }
            }, function (start, end, label) {
                console.log('New date range selected: ' + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD') + ' (predefined range: ' + label + ')');
            });
        }
    });

    /**
     * Time Pickers
     */
    $('.uk-datepicker').each(function (index) {
        debugger;
        var $elem = $(this);

        $elem.daterangepicker({
            singleDatePicker: true,
            timePicker: false,
            linkedCalendars: false,
            showCustomRangeLabel: false,
            locale: {
                format: 'MM/DD/YYYY'
            }
        }, function (start, end, label) {
            console.log('New date range selected: ' + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD') + ' (predefined range: ' + label + ')');
        });

    });

    /**
     * Parse Youtube Urls to get Video IDs
     */
    function youtube_parser(url) {
        var regExp = /^.*((youtu.be\/)|(v\/)|(\/u\/\w\/)|(embed\/)|(watch\?))\??v?=?([^#\&\?]*).*/;
        var match = url.match(regExp);
        return (match && match[7].length == 11) ? match[7] : false;
    }

    /**
     * Live Chat Users from Link/Button
     */

    $(document).on("click", "[data-message-user-id]", function () {
        var userId = $(this).attr('data-message-user-id');
        var message = $(this).attr('data-message');

        if (navigator.userAgent.indexOf('MSIE') !== -1 || navigator.appVersion.indexOf('Trident/') > 0) {
            var customEvent = document.createEvent("CustomEvent");
            customEvent.initCustomEvent('live-chat-user', false, false, {
                "userId": userId,
                "message": message,
            });
            window.dispatchEvent(customEvent);
        } else {
            window.dispatchEvent(new CustomEvent("live-chat-user", {"detail": {"userId": userId, "message": message}}))
        }
    })

    /**
     * Fire off a custom event to update all of the calendars on tab switching
     */
    $(document).on("click", ".uk-tab", function () {
        setTimeout(function () {
            if (navigator.userAgent.indexOf('MSIE') !== -1 || navigator.appVersion.indexOf('Trident/') > 0) {
                var customEvent = document.createEvent("CustomEvent");
                customEvent.initCustomEvent('uk-tab-clicked', false, false, {});
                window.dispatchEvent(customEvent);
            } else {
                window.dispatchEvent(new CustomEvent("uk-tab-clicked", {"detail": {}}))
            }
        }, 10);
    });


    /**
     * Capture click from school tab and handle it differently to allow for back button clicking
     *
     */


    $(document).on('click', '.schools-tab, .school-tab-tools', function (e) {
        e.preventDefault();
        const state = {'tab': 1}
        const title = ''
        const url = e.target.href

        window.history.pushState(state, title, url);
    });

    $(document).ready(function (e) {
        if (window.location.href.indexOf("dashboard") > -1) {
            loadSpecificTab();
        }
    });

    window.onpopstate = function (e) {
        loadSpecificTab();
    }

    function loadSpecificTab() {
        let params = new URLSearchParams(window.location.search);
        if (params.has('school')) {
            // Main school tabs
            let buttons = $('#school-tab').children().toArray();
            let tabs = $('#tab-schools').children().toArray();
            let school_id = params.get('school');
            $.each(tabs, function (index, elem) {
                if ($(elem).hasClass('school_' + school_id)) {
                    UIkit.switcher('#tab-schools').show(index);

                    $.each(buttons, function (index2, elem2) {
                        $(elem2).removeClass('uk-active');
                        if (index2 === index) {
                            $(elem2).addClass('uk-active');
                        }
                    });
                }
            });

            // Selected tool tabs
            if (params.has('tool')) {
                let tool = params.get('tool');
                let buttons = $('.school_' + school_id + " .school-tab-tools").children().toArray();
                let tabs = $("#tab-school-" + school_id + "-tools").children().toArray();

                $.each(tabs, function (index, elem) {
                    if ($(elem).hasClass("school_" + school_id + "_tools_" + tool)) {
                        UIkit.switcher('#tab-school-' + school_id + '-tools').show(index);

                        $.each(buttons, function (index2, elem2) {
                            $(elem2).removeClass('uk-active');
                            if (index2 === index) {
                                $(elem2).addClass('uk-active');
                            }
                        });
                    }
                });
            }
        }
    }


    $(document).on("click", ".delete-feedback", function (e) {
        e.preventDefault();
        UIkit.modal('#delete-modal').show();
        var id = $(this).data("id");
        $('#delete-feedback-request-form-holder').html('Loading...');

        // Get form request
        $.get('/dashboard/feedback/experiences/' + id + '/delete', {}, function (data) {
            $('#delete-feedback-request-form-holder').html(data);
            $('#delete-feedback-request-form-holder').append(`<input type="hidden" id="experience_id" value="${id}" />`);

            $('#delete-feedback-request-form-holder form div').addClass('uk-hidden');
        });

    });

    $(document).on('submit', '#delete-feedback-request', function (e) {
        // Post form data back

        var id = $('#experience_id').val();

        e.preventDefault();

        $.post('/dashboard/feedback/experiences/' + id + '/delete', $("#delete-feedback-request").serialize(), function () {
            $('#feedback_' + id).remove();
            UIkit.modal('#delete-modal').hide();
        });
    });

    /**
     * Stop videos when sidebar is closed
     */
    $('#offcanvas-help').on('hide.uk.offcanvas', function () {
        $('iframe').each(function () {
            $(this).attr('src', $(this).attr('src'));
        });
    });


    /**
     * Load tab if "page" is set
     */

    if (window.location.hash) {
        debugger;
        document.getElementById(window.location.hash.substr(1)).click();
        window.scrollTo(0, 0);
    }


    /**
     * Determine if career fields are filled in on event creation / edit
     */

    $('#filled-career-fields').on('click', function () {

    });


    /**
     * Sortable Lists
     */
    $('.ui-sortable').sortable({
        axis: 'y',
        update: function (event, ui) {
            var data = $(this).sortable('serialize');
            var url = $(this).data('url');
            $.post(url, {data: data}, function (data) {
                if (data.success === false) {
                    alert("Sorting failed to save. Please try again");
                }
            });
        }
    });

    $(document).on('moved', '.uk-sortable', function (event) {
        debugger;

        let sortableData = event.detail[0].items.map(el => el.id);

        console.log(event.detail[0]);
        console.log(sortableData);

        var url = $(this).data('url');
        debugger;
        $.post(url, {sortableData: sortableData}, function (data) {
            if (data.success === false) {
                alert("Sorting failed to save. Please try again");
            }
        });
    });


    /**
     * Mass User Actions (Delete / Deactivate)
     */
    $('.select-all-users').on('click', function () {
        var checked = $(this).prop('checked');
        $('.select-users').prop("checked", checked);

        toggle_mass_action_button();
    });

    $('.select-users').on('click', function () {
        toggle_mass_action_button();
    });

    $('#mass-delete').on('click', function () {
        var ans = confirm('Are you sure you want to delete these users?');
        if (ans) {
            create_mass_action_form('/dashboard/profiles/mass-delete');
        }
    });

    $('#mass-deactivate').on('click', function () {
        var ans = confirm('Are you sure you want to deactivate these users?');
        if (ans) {
            create_mass_action_form('/dashboard/profiles/mass-deactivate');
        }
    });


    $('.load-help-video').on('click', function () {
        var id = $(this).data('id');
        var video = $(this).data('video');

        $('#video_' + id).html('<iframe src="https://www.youtube.com/embed/' + video + '" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;"></iframe>');
    });

    function create_mass_action_form(action) {
        var form = document.createElement('form');
        form.setAttribute('method', 'post');
        form.setAttribute('action', action);

        $('.select-users:checked').each(function () {
            var field = document.createElement('input');
            field.setAttribute('type', 'hidden');
            field.setAttribute('name', 'user_id[]');
            field.setAttribute('value', $(this).val());

            form.appendChild(field);
        });

        var field = document.createElement('input');
        field.setAttribute('type', 'hidden');
        field.setAttribute('name', 'user_role');
        field.setAttribute('value', $('.select-users-role').val());

        form.appendChild(field);


        document.body.appendChild(form);
        form.submit();
    }

    function toggle_mass_action_button() {
        var length = $('.select-users:checked').length;
        if (length > 0) {
            $('#mass-actions').show();
        } else {
            $('#mass-actions').hide();
        }
    }
});
