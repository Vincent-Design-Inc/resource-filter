jQuery(document).ready(function ($) {
  /**
   * Trigger filtering for resources.
   * Handles collecting form data, updating the "Filters Used" section,
   * and sending an AJAX request to fetch filtered results.
   *
   * @param {number} paged The current page number (default is 1).
   */
  window.triggerFiltering = function (paged = 1) { // Attach to the window object
    let searchTerm = $('#search').val();
    let appliedFilters = [];

    if (searchTerm) {
      appliedFilters.push(
        `<span class="filter-item" data-type="search" data-value="${searchTerm}">
          <strong>Search:</strong> ${searchTerm}
          <button class="remove-filter" aria-label="Remove search term">×</button>
        </span>`
      );
    }

    // Collect selected taxonomy filters dynamically
    let taxonomyFilters = {};
    $('input[type="checkbox"]:checked').each(function () {
      let taxonomy = $(this).attr('name').replace('[]', ''); // Extract taxonomy name

      if (!taxonomyFilters[taxonomy]) {
        taxonomyFilters[taxonomy] = [];
      }

      taxonomyFilters[taxonomy].push({
      value: $(this).val(),
      text: $(this).closest('label').text().trim() // Get the text associated with the checkbox
      });
    });

    const toTitleCase = (phrase) => {
      return phrase
        .toLowerCase()
        .split('_')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
    };

    // Build applied filters for display
    let dropdownFilters = [];
    let finalFilters = {};

    for (let taxonomy in taxonomyFilters) {
      taxonomyFilters[taxonomy].forEach(function (term) {
        let taxName = toTitleCase(taxonomy);

        appliedFilters.push(
          `<span class="filter-item" data-type="${taxonomy}" data-value="${term.value}">
            <strong>${taxName}:</strong> ${term.text}
            <button class="remove-filter" aria-label="Remove ${term.text}">×</button>
          </span>`
        );

        dropdownFilters.push(term.text);

        if (!finalFilters[taxonomy]) {
          finalFilters[taxonomy] = [];
        }

        finalFilters[taxonomy].push(
          term.value,
        );

        // $(`#${taxonomy}_text`).html(
        //   dropdownFilters ? dropdownFilters.join(', ') : taxName
        // );
      });
    }

    $('#applied-filters').html(appliedFilters.length ? appliedFilters.join(' ') : 'None');

    let formData = {
      action: 'filter_resources',
      nonce: resourceFilterAjax.nonce,
      search: searchTerm,
      paged: paged,
      sort_order: $('#sortOrder').val(),
      ...finalFilters, // Include taxonomy filters dynamically
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
  };


  // Handle sort order change
  $('#sortOrder').on('change', function () {
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

  // Handle pagination link clicks.
  $(document).on('click', '.pagination a', function (e) {
    e.preventDefault();

    let pagedMatch = $(this).attr('href').match(/paged=(\d+)/);
    let paged = pagedMatch ? parseInt(pagedMatch[1], 10) : 1;

    triggerFiltering(paged);
  });

  // Handle removing individual filters from the "Filters Used" section.
  $(document).on('click', '.remove-filter', function (e) {
    e.preventDefault();

    let $filter = $(this).closest('.filter-item');
    let filterType = $filter.data('type');
    let filterValue = $filter.data('value');

    // Remove the corresponding filter
    if (filterType === 'search') {
      $('#search').val('');
    } else {
      // Dynamically handle taxonomy filters
      $(`input[name="${filterType}[]"]:checked`).each(function () {
        if ($(this).val() === filterValue) { // Match the slug, not the name
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

      // Close all other dropdowns and update aria-expanded
      document.querySelectorAll('.custom-dropdown').forEach(function (otherDropdown) {
        if (otherDropdown !== dropdown) {
          otherDropdown.classList.remove('open');
          otherDropdown.querySelector('.dropdown-toggle').setAttribute('aria-expanded', 'false');
        }
      });

      // Toggle the current dropdown and update aria-expanded
      const isOpen = dropdown.classList.toggle('open');
      this.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });

    // Close dropdown when tabbing away
    button.parentElement.addEventListener('focusout', function (event) {
      const dropdown = this;
      const relatedTarget = event.relatedTarget;

      // Check if the newly focused element is outside the dropdown
      if (!dropdown.contains(relatedTarget)) {
        dropdown.classList.remove('open');
        dropdown.querySelector('.dropdown-toggle').setAttribute('aria-expanded', 'false');
      }
    });
  });

  // Close dropdowns when clicking outside
  document.addEventListener('click', function (event) {
    if (!event.target.closest('.custom-dropdown')) {
      document.querySelectorAll('.custom-dropdown').forEach(function (dropdown) {
        dropdown.classList.remove('open');
        dropdown.querySelector('.dropdown-toggle').setAttribute('aria-expanded', 'false');
      });
    }
  });

  // Close dropdowns when pressing the Escape key
  document.addEventListener('keyup', function (event) {
    if (event.key === 'Escape') {
      document.querySelectorAll('.custom-dropdown').forEach(function (dropdown) {
        dropdown.classList.remove('open');
        dropdown.querySelector('.dropdown-toggle').setAttribute('aria-expanded', 'false');
      });
    }
  });
});
