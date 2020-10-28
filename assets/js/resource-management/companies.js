jQuery(document).ready(function($) {

  $(document).on('click', '#modal-add-company-resource [data-action]', function(e) {
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

  // Get selected resource item information.
  $(document).on('click', '.company-edit-resource', function(e){
    e.preventDefault();
    var item = $(this);

    $.post('/dashboard/companies/resource/' + item.data('id') + '/get', {}, function(ret){
      if(ret.success) {
        $('#edit_company_edit_resource_title').val(ret.title);
        $('#edit_company_edit_resource_description').val(ret.description);
        $('#edit_company_edit_resource_link_to_website').val(ret.website);
        $('#edit-submit-button').attr("data-action", "/dashboard/companies/resource/" + ret.id + "/edit");
        $('#edit_company_edit_resource_id').val(ret.id);
        UIkit.modal('#modal-edit-company-resource').show();
      } else {
        alert("Error getting resource");
      }
    });
  });

  $(document).on('click', '#modal-edit-company-resource [data-action]', function(e) {
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

          UIkit.modal('#modal-edit-company-resource').hide();
          window.Pintex.notification("Resource saved.", "success");
        } else {
          window.Pintex.notification("Unable to save resource. Please try again.", "danger");
        }
      }
    });
  });

});