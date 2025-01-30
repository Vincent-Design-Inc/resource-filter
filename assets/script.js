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
      response = JSON.parse(response);

      $('#resource-results').html(response.html);
      $('#result-count').text(response.count);

      let filters = [];
      if (response.filters.search) filters.push('Search: "' + response.filters.search + '"');
      if (response.filters.resource_type) filters.push('Type: ' + $('#resource_type option:selected').text());
      if (response.filters.resource_subject) filters.push('Subject: ' + $('#resource_subject option:selected').text());

      $('#applied-filters').text(filters.length ? filters.join(', ') : 'None');
    });
  });
});
