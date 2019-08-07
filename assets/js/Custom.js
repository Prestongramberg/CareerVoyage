import Quill from 'quill';

jQuery(document).ready(function($) {

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
                    //console.log('error', arguments);
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
                                        _template.replace(/UPLOAD_ID/g, 0).replace(/UPLOAD_URL/g, window.SETTINGS.BASE_URL + response.url)
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
    $('form .uk-switcher').each(function() {
        var $tab = $(this).children().has('ul:not(".ql-container ul")').first();
        if( $tab.length > 0 ) {
            var index = $tab.index();
            $(this).children().removeClass('uk-active').eq(index).addClass('uk-active');
            $("[uk-tab*=" + $(this).attr('id') + "]").children().removeClass('uk-active').eq(index).addClass('uk-active');
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
    })

});