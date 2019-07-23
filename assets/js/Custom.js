import Quill from 'quill';

jQuery(document).ready(function($) {

    // Smooth Page Transitions
    $('body').addClass('ready');

    // WYSIWYG
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

});