jQuery(document).ready(function ($) {
  $('#resource-filter').on('submit', function (e) {
    e.preventDefault();

    let formData = {
      action: 'filter_resources',
      nonce: resourceFilterAjax.nonce,
      search: $('#search').val(),
      resource_type: $('#resource_type').val(),
      resource_subject: $('#resource_subject').val(),
    };

    $.post(resourceFilterAjax.ajaxurl, formData, function (response) {
      $('#resource-results').html(response);
    });
  });
});
