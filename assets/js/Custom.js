import Quill from 'quill';

jQuery(document).ready(function($) {

    // Smooth Page Transitions
    $('body').addClass('ready');

    // WYSIWYG
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
            const editor = new Quill(`.${uniqueClass}`, {
                theme: 'snow'
            });

            // Populate the editor with any content that already exists from target
            editor.root.innerHTML = $target.val();

            // Listen for events and update the target
            editor.on('text-change', function(delta, oldDelta, source) {
                $target.val(
                    editor.root.innerHTML
                );
            });

            // Increment the unique instance
            instance++;

        })($(this));
    });

});