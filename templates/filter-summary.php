<?php
if (!defined('ABSPATH')) { exit; } // Prevent direct access

error_log('Filter Summary: ' . print_r($_POST, true));

// Get count from AJAX or direct POST
$count = isset($resTotal) ? esc_html($resTotal) : 0;

// Initialize filters array
$filters = [];

// Handle the "Search" filter
if (!empty($_POST['search'])) {
  $filters[] = [
    'type' => 'search',
    'value' => esc_html($_POST['search']),
    'label' => '<strong>Search:</strong> "' . esc_html($_POST['search']) . '"'
  ];
}

// Handle the "Resource Type" filter
if (!empty($_POST['resource_type'])) {
  $selected_types = is_array($_POST['resource_type']) ? $_POST['resource_type'] : [$_POST['resource_type']];
  $filters[] = [
    'type' => 'resource_type',
    'value' => esc_html(implode(',', $selected_types)),
    'label' => '<strong>Type:</strong> ' . esc_html(implode(', ', $selected_types))
  ];
}

// Handle the "Resource Subject" filter
if (!empty($_POST['resource_subject'])) {
  $selected_subjects = is_array($_POST['resource_subject']) ? $_POST['resource_subject'] : [$_POST['resource_subject']];
  $filters[] = [
    'type' => 'resource_subject',
    'value' => esc_html(implode(',', $selected_subjects)),
    'label' => '<strong>Subject:</strong> ' . esc_html(implode(', ', $selected_subjects))
  ];
}

// Display filters as HTML
$filter_html = '';
if (!empty($filters)) {
  foreach ($filters as $filter) {
    $filter_html .= '<span class="filter-item" data-type="' . esc_attr($filter['type']) . '" data-value="' . esc_attr($filter['value']) . '">'
      . $filter['label']
      . ' <button class="remove-filter" aria-label="Remove ' . esc_attr($filter['type']) . '">×</button>'
      . '</span> ';
  }
} else {
  $filter_html = 'None';
}
?>

<div id="resource-filter-summary" class="flex justify-between w-full">
  <!-- Resource Count -->
  <p><strong>Showing <span id="result-count"><?php echo esc_html($count); ?></span> resource(s)</strong></p>

  <div class="sort-filters flex items-start gap-4">
    <!-- Sort Container -->
    <div id="sort-container">
      <label for="sort-order">Sort by:</label>
      <select id="sort-order">
        <option value="date_desc" <?php selected(isset($_GET['sort_order']) ? $_GET['sort_order'] : '', 'date_desc'); ?>>Newest First</option>
        <option value="date_asc" <?php selected(isset($_GET['sort_order']) ? $_GET['sort_order'] : '', 'date_asc'); ?>>Oldest First</option>
        <option value="title_asc" <?php selected(isset($_GET['sort_order']) ? $_GET['sort_order'] : '', 'title_asc'); ?>>Title (A-Z)</option>
        <option value="title_desc" <?php selected(isset($_GET['sort_order']) ? $_GET['sort_order'] : '', 'title_desc'); ?>>Title (Z-A)</option>
      </select>
    </div>

    <!-- Applied Filters -->
    <p>
      <strong>Filters applied:</strong><br>
      <span id="applied-filters"><?php echo $filter_html; ?></span>
    </p>
  </div>
</div>
