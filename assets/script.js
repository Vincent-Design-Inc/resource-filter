jQuery(document).ready(function ($) {
  function triggerFiltering() {
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

    if (searchTerm) {
      appliedFilters.push(
        `<span class="filter-item" data-type="search" data-value="${searchTerm}">
          <strong>Search:</strong> "${searchTerm}"
          <button class="remove-filter" aria-label="Remove Search">×</button>
        </span>`
      );
    }

    if (selectedTypes.length > 0) {
      appliedFilters.push(
        `<span class="filter-item" data-type="resource_type" data-value="${selectedTypes.join(
          ', '
        )}">
          <strong>Type:</strong> ${selectedTypes.join(', ')}
          <button class="remove-filter" aria-label="Remove Type">×</button>
        </span>`
      );
    }

    if (selectedSubjects.length > 0) {
      appliedFilters.push(
        `<span class="filter-item" data-type="resource_subject" data-value="${selectedSubjects.join(
          ', '
        )}">
          <strong>Subject:</strong> ${selectedSubjects.join(', ')}
          <button class="remove-filter" aria-label="Remove Subject">×</button>
        </span>`
      );
    }

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
    };

    $.post(resourceFilterAjax.ajaxurl, formData, function (response) {
      response = JSON.parse(response);

      $('#resource-results').html(response.html);
      $('#result-count').text(response.count);

      if (response.pagination) {
        $('.pagination').html(response.pagination.join(''));
      }
    });
  }

  // Handle filter removal
  $(document).on('click', '.remove-filter', function (e) {
    e.preventDefault();

    let $filter = $(this).closest('.filter-item');
    let filterType = $filter.data('type');
    let filterValue = $filter.data('value');

    // Clear the relevant filter
    if (filterType === 'search') {
      $('#search').val('');
    } else if (filterType === 'resource_type') {
      $('input[name="resource_type[]"]:checked').each(function () {
        if ($(this).closest('label').text().trim() === filterValue) {
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
    triggerFiltering();
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
