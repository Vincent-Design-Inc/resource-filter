jQuery(document).ready(function ($) {
  /** Triggers filtering and pagination of resources based on the current form state.
   *
   * @param {number} [paged=1] - The page number to query.
   */
  function triggerFiltering(paged = 1) {
    let searchTerm = $('#search').val();

    let selectedTypes = $('input[name="resource_type[]"]:checked')
      .map(function () {
        return $(this).closest('label').text().trim();
      })
      .get();

    let selectedSubjects = $('input[name="resource_subject[]"]:checked')
      .map(function () {
        return $(this).closest('label').text().trim();
      })
      .get();

    let appliedFilters = [];

    // Search Term
    if (searchTerm) {
      appliedFilters.push(
        `<span class="filter-item" data-type="search" data-value="${searchTerm}">
          <strong>Search:</strong> "${searchTerm}"
          <button class="remove-filter" aria-label="Remove Search">×</button>
        </span>`
      );
    }

    // Resource Types
    $('input[name="resource_type[]"]:checked').each(function () {
      const slug = $(this).val(); // Get the slug
      const name = $(this).closest('label').text().trim(); // Get the name

      appliedFilters.push(
        `<span class="filter-item" data-type="resource_type" data-value="${slug}">
          <strong>Type:</strong> ${name}
          <button class="remove-filter" aria-label="Remove Type ${name}">×</button>
        </span>`
      );
    });

    // Resource Subjects
    selectedSubjects.forEach(function (subject) {
      appliedFilters.push(
        `<span class="filter-item" data-type="resource_subject" data-value="${subject}">
          <strong>Subject:</strong> ${subject}
          <button class="remove-filter" aria-label="Remove Subject ${subject}">×</button>
        </span>`
      );
    });

    $('#applied-filters').html(
      appliedFilters.length ? appliedFilters.join(' ') : 'None'
    );

    let formData = {
      action: 'filter_resources',
      nonce: resourceFilterAjax.nonce,
      search: $('#search').val(),
      resource_type: $('input[name="resource_type[]"]:checked')
        .map(function () {
          return this.value;
        })
        .get(),
      resource_subject: $('input[name="resource_subject[]"]:checked')
        .map(function () {
          return this.value;
        })
        .get(),
      sort_order: $('#sort-order').val(),
      paged: paged,
    };

    $.post(resourceFilterAjax.ajaxurl, formData, function (response) {
      response = JSON.parse(response);

      $('#resource-results').html(response.html);
      $('#result-count').text(response.count);

      // Update pagination
      if (response.pagination && response.pagination.length > 0) {
        // Clear and update pagination container
        if (!$('.pagination').length) {
          $('#resource-results').after('<div class="pagination"></div>');
        }
        $('.pagination').html(response.pagination.join(''));
      } else {
        $('.pagination').html(''); // Clear pagination if no links are needed
      }
    });
  }

  // Handle filter removal
  $(document).on('click', '.remove-filter', function (e) {
    e.preventDefault();

    let $filter = $(this).closest('.filter-item');
    let filterType = $filter.data('type');
    let filterValue = $filter.data('value');

    // Remove the corresponding filter
    if (filterType === 'search') {
      $('#search').val('');
    } else if (filterType === 'resource_type') {
      $('input[name="resource_type[]"]:checked').each(function () {
        if ($(this).val() === filterValue) { // Match the slug, not the name
          $(this).prop('checked', false);
        }
      });
    } else if (filterType === 'resource_subject') {
      $('input[name="resource_subject[]"]:checked').each(function () {
        if ($(this).closest('label').text().trim() === filterValue) {
          $(this).prop('checked', false);
        }
      });
    }

    // Re-trigger filtering after removing the filter
    triggerFiltering(1);
  });

  // Handle form submission
  $('#resource-filter').on('submit', function (e) {
    e.preventDefault();
    triggerFiltering();
  });

  // Handle sort order change
  $('#sort-order').on('change', function () {
    triggerFiltering();
  });

  // Handle pagination click
  $(document).on('click', '.pagination a', function (e) {
    e.preventDefault();

    // Extract the page number from the link
    let pagedMatch = $(this).attr('href').match(/paged=(\d+)/);
    let paged = pagedMatch ? pagedMatch[1] : 1; // Default to page 1 if no match is found

    // Trigger filtering for the selected page
    triggerFiltering(paged);
  });
});

document.addEventListener('DOMContentLoaded', function () {
  // Toggle dropdown visibility
  document.querySelectorAll('.custom-dropdown .dropdown-toggle').forEach(function (button) {
    button.addEventListener('click', function () {
      const dropdown = this.parentElement;

      // Close all other dropdowns
      document.querySelectorAll('.custom-dropdown').forEach(function (otherDropdown) {
        if (otherDropdown !== dropdown) {
          otherDropdown.classList.remove('open');
        }
      });

      // Toggle the current dropdown
      dropdown.classList.toggle('open');
    });
  });

  // Close dropdowns when clicking outside
  document.addEventListener('click', function (event) {
    if (!event.target.closest('.custom-dropdown')) {
      document.querySelectorAll('.custom-dropdown').forEach(function (dropdown) {
        dropdown.classList.remove('open');
      });
    }
  });
});
