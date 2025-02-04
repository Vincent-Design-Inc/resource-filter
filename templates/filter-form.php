<?php
if (!defined('ABSPATH')) { exit; } // Prevent direct access

$resource_types    = get_terms(['taxonomy' => 'resource_type', 'hide_empty' => true]);
$resource_subjects = get_terms(['taxonomy' => 'resource_subject', 'hide_empty' => true]);
?>

<form id="resource-filter">
  <!-- Search Field -->
  <div class="search-text">
    <input type="text" id="search" name="search" placeholder="Search resources..." class="full-width">
    <button type="submit">Filter</button>
  </div>

  <div class="search-tax">
    <!-- Resource Type Filters -->
    <details>
      <summary>Resource Type</summary>

      <div class="filter-options">
        <?php foreach ($resource_types as $type) : ?>
          <label>
            <input type="checkbox" name="resource_type[]" value="<?php echo esc_attr($type->slug); ?>">
            <?php echo esc_html($type->name); ?>
          </label>
        <?php endforeach; ?>
      </div>
    </details>

    <!-- Resource Subject Filters -->
    <details>
      <summary>Resource Subject</summary>

      <div class="filter-options">
        <?php foreach ($resource_subjects as $subject) : ?>
          <label>
            <input type="checkbox" name="resource_subject[]" value="<?php echo esc_attr($subject->slug); ?>">
            <?php echo esc_html($subject->name); ?>
          </label>
        <?php endforeach; ?>
      </div>
    </details>
  </div>
</form>
