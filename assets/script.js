jQuery(document).ready(function ($) {
  function triggerFiltering() {
    let searchTerm = $('#search').val();

    let selectedTypes = $('input[name="resource_type[]"]:checked').map(function () {
      return $(this).closest('label').text().trim();
    }).get();

    let selectedSubjects = $('input[name="resource_subject[]"]:checked').map(function () {
      return $(this).closest('label').text().trim();
    }).get();

    let appliedFilters = [];

    if (searchTerm) appliedFilters.push(`<strong>Search:</strong> "${searchTerm}"`);
    if (selectedTypes.length > 0) appliedFilters.push(`<strong>Type:</strong> ${selectedTypes.join(', ')}`);
    if (selectedSubjects.length > 0) appliedFilters.push(`<strong>Subject:</strong> ${selectedSubjects.join(', ')}`);

    $('#applied-filters').html(appliedFilters.length ? appliedFilters.join('<br>') : 'None');

    let formData = {
      action: 'filter_resources',
      nonce: resourceFilterAjax.nonce,
      search: $('#search').val(),
      resource_type: $('input[name="resource_type[]"]:checked').map(function () {
        return this.value;
      }).get(),
      resource_subject: $('input[name="resource_subject[]"]:checked').map(function () {
        return this.value;
      }).get(),
      sort_order: $('#sort-order').val() // Pass sorting option
    };

    $.post(resourceFilterAjax.ajaxurl, formData, function (response) {
      response = JSON.parse(response);

      $('#resource-results').html(response.html);

      if (response.count !== undefined) {
        $('#result-count').text(response.count);
      } else {
        $('#result-count').text($('#result-count').text()); // Keep initial count
      }

      // Update sort order label
      let sortText = $('#sort-order option:selected').text();
      $('#sort-label').text(sortText);
    });
  }

  // Handle form submission
  $('#resource-filter').on('submit', function (e) {
    e.preventDefault();
    triggerFiltering();
  });

  // Handle sorting change
  $('#sort-order').on('change', function () {
    triggerFiltering();
  });
});

// Close <details> elements when clicking outside
jQuery(document).on('click', function (event) {
  jQuery('details[open]').each(function () {
    if (!jQuery(this).is(event.target) && jQuery(this).has(event.target).length === 0) {
      jQuery(this).removeAttr('open');
    }
  });
});
