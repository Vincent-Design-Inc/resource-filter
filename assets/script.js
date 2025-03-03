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

    let appliedFilters  = [];
    let typeFilters    = [];
    let subjectFilters = [];

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
          <button class="remove-filter" aria-label="Remove ${name}">×</button>
        </span>`
      );

      typeFilters.push(
        `${name}`
      );
    });

    // Resource Subjects
    selectedSubjects.forEach(function (subject) {
      appliedFilters.push(
        `<span class="filter-item" data-type="resource_subject" data-value="${subject}">
          <strong>Subject:</strong> ${subject}
          <button class="remove-filter" aria-label="Remove ${subject}">×</button>
        </span>`
      );

      subjectFilters.push(
        `${subject}`
      );
    });

    $('#applied-filters').html(
      appliedFilters.length ? appliedFilters.join(' ') : 'None'
    );

    $('#type_text').html(
      typeFilters.length ? typeFilters.join(', ') : 'Resource Type'
    );
    $('#subject_text').html(
      subjectFilters.length ? subjectFilters.join(', ') : 'Subject Tags'
    );

    let formData = {
      action: 'filter_resources',
      nonce: resourceFilterAjax.nonce,
      search: searchTerm,
      paged: paged,
      sort_order: $('#sort-order').val(),
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
    };

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
  $('#homepage-filter').on('submit', function (e) {
    e.preventDefault();
    triggerFiltering(1); // Start at page 1 on new form submission
  });

  // Handle sort order change
  $('#sort-order').on('change', function () {
    triggerFiltering();
  });

  // Trigger filtering when dropdowns or checkboxes change
  $('#resource-filter select, #resource-filter input[type="checkbox"]').on('change', function () {
    triggerFiltering(1);
  });

  // Allow the search field to be submitted manually
  $('#resource-filter').on('submit', function (e) {
    e.preventDefault();
    triggerFiltering(1);
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
