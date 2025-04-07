<?php
if (!defined('ABSPATH')) { exit; } // Prevent direct access

// Get count from AJAX or direct POST
global $postsTotal;
$count = isset($postsTotal) ? esc_html($postsTotal) : 0;

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

// Get selected taxonomies from admin settings
$selectedTaxonomies = get_option('content_filter_taxonomies', []);

// Handle dynamic taxonomy filters
foreach ($selectedTaxonomies as $taxonomy) {
  if (!empty($_POST[$taxonomy])) {
    $selectedTerms = is_array($_POST[$taxonomy]) ? $_POST[$taxonomy] : [$_POST[$taxonomy]];
    $taxonomyObj = get_taxonomy($taxonomy);

    if ($taxonomyObj) {
      $filters[] = [
        'type' => $taxonomy,
        'value' => esc_html(implode(',', $selectedTerms)),
        'label' => '<strong>' . esc_html($taxonomyObj->labels->singular_name) . ':</strong> ' . esc_html(implode(', ', $selectedTerms))
      ];
    }
  }
}

// Display filters as HTML
$filterHtml = '';
if (!empty($filters)) {
  foreach ($filters as $filter) {
    $filterHtml .= '<span class="filter-item" data-type="' . esc_attr($filter['type']) . '" data-value="' . esc_attr($filter['value']) . '">'
      . $filter['label']
      . ' <button class="remove-filter" aria-label="Remove ' . esc_attr($filter['type']) . '">×</button>'
      . '</span> ';
  }
} else {
  $filterHtml = 'None';
}
?>

<div id="resource-filter-summary" class="flex justify-between w-full">
  <!-- Resource Count -->
  <p><strong>Showing <span id="result-count"><?php echo esc_html($count); ?></span> resource(s)</strong></p>

  <div class="sort-filters flex items-start gap-4">
    <!-- Applied Filters -->
    <p>
      <strong>Filters applied:</strong><br>
      <span id="applied-filters"><?php echo $filterHtml; ?></span>
    </p>
  </div>

  <!-- Sort Container -->
  <div id="sort-container">
    <label for="sortOrder"><strong>Sort by:</strong></label>
    <select id="sortOrder" name="sortOrder">
      <option value="date_desc" <?php selected(isset($_GET['sortOrder']) ? $_GET['sortOrder'] : '', 'date_desc'); ?>>Newest First</option>
      <option value="date_asc" <?php selected(isset($_GET['sortOrder']) ? $_GET['sortOrder'] : '', 'date_asc'); ?>>Oldest First</option>
      <option value="title_asc" <?php selected(isset($_GET['sortOrder']) ? $_GET['sortOrder'] : '', 'title_asc'); ?>>Title (A-Z)</option>
      <option value="title_desc" <?php selected(isset($_GET['sortOrder']) ? $_GET['sortOrder'] : '', 'title_desc'); ?>>Title (Z-A)</option>
    </select>
  </div>
</div>
