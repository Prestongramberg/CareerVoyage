jQuery(document).ready(function($) {
  
  $(document).on('click', '#modal-add-lesson-resource [data-action]', function(e) {
    e.preventDefault();

    const url = $(this).attr('data-action');
    const $modalBody = $(this).closest('.uk-modal-body');
    const $fields = $modalBody.find('[name]');
    const $titleField = $modalBody.find('[name="title"]');
    const $descriptionField = $modalBody.find('[name="description"]');
    const $fileField = $modalBody.find('[name="resource"]');
    const $linkToWebsite = $modalBody.find('[name="linkToWebsite"]');


    var formData = new FormData();
    formData.append('title', $titleField.val() );
    formData.append('description', $descriptionField.val() );
    formData.append('resource', $fileField[0].files[0]);
    formData.append('linkToWebsite', $linkToWebsite.val() );

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

  // Get selected resource item information.
  $(document).on('click', '.lesson-edit-resource', function(e){
    e.preventDefault();
    var item = $(this);

    $.post('/dashboard/lessons/file/' + item.data('id') + '/get', {}, function(ret){
      if(ret.success) {
        $('#edit_lesson_edit_resource_title').val(ret.title);
        $('#edit_lesson_edit_resource_description').val(ret.description);
        $('#edit_lesson_edit_resource_link_to_website').val(ret.website);
        $('#edit-submit-button').attr("data-action", "/dashboard/lessons/file/" + ret.id + "/edit");
        $('#edit_lesson_edit_resource_id').val(ret.id);
        UIkit.modal('#modal-edit-lesson-resource').show();
      } else {
        alert("Error getting resource");
      }
    });
  });

  $(document).on('click', '#modal-edit-lesson-resource [data-action]', function(e) {
    e.preventDefault();

    const url = $(this).attr('data-action');
    const $modalBody = $(this).closest('.uk-modal-body');
    const $fields = $modalBody.find('[name]');
    const $titleField = $modalBody.find('[name="title"]');
    const $descriptionField = $modalBody.find('[name="description"]');
    const $linkToWebsiteField = $modalBody.find('[name="linkToWebsite"]');
    const $fileField = $modalBody.find('[name="resource"]');
    const $resourceId = $modalBody.find('[name="resource_id"]');

    var formData = new FormData();
    formData.append('title', $titleField.val() );
    formData.append('description', $descriptionField.val() );
    formData.append('resource', $fileField[0].files[0]);
    formData.append('linkToWebsite', $linkToWebsiteField.val() );

    $modalBody.find('[name="resource"]').val('');

    $.ajax({
      url: url,
      data: formData,
      contentType: false,
      processData: false,
      type: "POST",
      complete: function (serverResponse) {

        const response = serverResponse.responseJSON;
        if (response.success) {
          $('#resource_' + $resourceId.val() + ' dt').html(response.title);
          $('#resource_' + $resourceId.val() + ' dd').html(response.description);
          $('#resource_' + $resourceId.val() + ' .view').attr('href', response.url);

          UIkit.modal('#modal-edit-lesson-resource').hide();
          window.Pintex.notification("Resource saved.", "success");
        } else {
          window.Pintex.notification("Unable to save resource. Please try again.", "danger");
        }
      }
    });
  });
});