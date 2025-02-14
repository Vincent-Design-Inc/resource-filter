jQuery(document).ready(function ($) {
  /**
   * Trigger filtering for resources.
   * Handles collecting form data, updating the "Filters Used" section,
   * and sending an AJAX request to fetch filtered results.
   *
   * @param {number} paged The current page number (default is 1).
   */
  function triggerFiltering(paged = 1) {
    let searchTerm = $('#search').val();

    let appliedFilters = [];
    let formData = {
      action: 'filter_resources',
      nonce: resourceFilterAjax.nonce,
      search: searchTerm,
      paged: paged,
      sort_order: $('#sort-order').val(),
      taxonomies: {}, // Store selected taxonomy terms
    };

    // Dynamically handle all taxonomy inputs
    $('.taxonomy-filter').each(function () {
      let taxonomy = $(this).data('taxonomy');
      let selectedTerms = $(this).find('input:checked').map(function () {
        return this.value;
      }).get();

      if (selectedTerms.length > 0) {
        formData.taxonomies[taxonomy] = selectedTerms;

        // Add to applied filters
        selectedTerms.forEach(function (term) {
          appliedFilters.push(
            `<span class="filter-item" data-type="taxonomy" data-taxonomy="${taxonomy}" data-value="${term}">
              <strong>${taxonomy}:</strong> ${term}
              <button class="remove-filter" aria-label="Remove ${taxonomy} ${term}">×</button>
            </span>`
          );
        });
      }
    });

    // Include search term in applied filters
    if (searchTerm) {
      appliedFilters.push(
        `<span class="filter-item" data-type="search" data-value="${searchTerm}">
          <strong>Search:</strong> "${searchTerm}"
          <button class="remove-filter" aria-label="Remove Search">×</button>
        </span>`
      );
    }

    // Update "Filters Used" section
    $('#applied-filters').html(appliedFilters.length ? appliedFilters.join(' ') : 'None');

    // Perform AJAX request
    $.post(resourceFilterAjax.ajaxurl, formData, function (response) {
      response = JSON.parse(response);

      $('#resource-results').html(response.html);
      $('#result-count').text(response.count || 0);

      // Update pagination
      if (response.pagination && response.pagination.length > 0) {
        $('.pagination').html(response.pagination.join(''));
      } else {
        $('.pagination').html('');
      }
    });
  }

  /**
   * Handle form submission for filtering resources.
   */
  $('#resource-filter','#homepage-filter').on('submit', function (e) {
    e.preventDefault();
    triggerFiltering(1); // Start at page 1 on new form submission
  });

  /**
   * Handle pagination link clicks.
   */
  $(document).on('click', '.pagination a', function (e) {
    e.preventDefault();

    let pagedMatch = $(this).attr('href').match(/paged=(\d+)/);
    let paged = pagedMatch ? parseInt(pagedMatch[1], 10) : 1;

    triggerFiltering(paged);
  });

  /**
   * Handle removing individual filters from the "Filters Used" section.
   */
  $(document).on('click', '.remove-filter', function () {
    let $filter = $(this).closest('.filter-item');
    let filterType = $filter.data('type');
    let filterValue = $filter.data('value');

    if (filterType === 'taxonomy') {
      let taxonomy = $filter.data('taxonomy');
      $(`.taxonomy-filter[data-taxonomy="${taxonomy}"] input:checked`).each(function () {
        if ($(this).val() === filterValue) {
          $(this).prop('checked', false);
        }
      });
    } else if (filterType === 'search') {
      $('#search').val('');
    }

    triggerFiltering(1); // Refresh results starting at page 1
  });
});
