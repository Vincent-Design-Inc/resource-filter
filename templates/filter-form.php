<?php
if (!defined('ABSPATH')) { exit; } // Prevent direct access

$resource_types    = get_terms(['taxonomy' => 'resource_type', 'hide_empty' => true]);
$resource_subjects = get_terms(['taxonomy' => 'resource_subject', 'hide_empty' => true]);
?>

<form class="px-4 sm:px-0" id="resource-filter">
  <!-- Search Field -->
  <div class="search-text">
    <input class="bg-[#F8F8F8] border-[#8B8B8B] border-2" type="text" id="search" name="search" placeholder="Search resources..."
      value="<?php echo isset($search) ? esc_attr($search) : ''; ?>">
    <button class="bg-[#3B65D4] text-white" type="submit">Search</button>
    <button type="reset" id="clear-search">&times;</button>
  </div>

  <div class="search-tax">
    <!-- Resource Type Filters -->
    <div class="custom-dropdown">
      <button type="button" class="dropdown-toggle">Resource Type</button>
      <div class="dropdown-menu">
        <?php foreach ($resource_types as $type) : ?>
        <label>
          <input type="checkbox" name="resource_type[]" value="<?php echo esc_attr($type->slug); ?>" <?php echo
            (isset($_POST['resource_type']) && in_array($type->slug, (array) $_POST['resource_type'])) ? 'checked' :
          '';
          ?>>
          <?php echo esc_html($type->name); ?>
        </label>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Resource Subject Filters -->
    <div class="custom-dropdown">
      <button type="button" class="dropdown-toggle">Resource Subject</button>
      <div class="dropdown-menu">
        <?php foreach ($resource_subjects as $subject) : ?>
        <label>
          <input type="checkbox" name="resource_subject[]" value="<?php echo esc_attr($subject->slug); ?>" <?php echo
            (isset($_POST['resource_subject']) && in_array($subject->slug, (array) $_POST['resource_subject'])) ?
          'checked' : ''; ?>>
          <?php echo esc_html($subject->name); ?>
        </label>
        <?php endforeach; ?>
      </div>
    </div>
    <!-- Sort Container -->
      <div id="sort-container" class="flex items-start">
        <label class="pt-2" for="sort-order">Sort:</label>
        <select class="ml-2 bg-white border-[#CCC] border-2" id="sort-order">
          <option value="date_desc" <?php selected(isset($_GET['sort_order']) ? $_GET['sort_order'] : 'date_desc', 'date_desc'); ?>>Newest First</option>
          <option value="date_asc" <?php selected(isset($_GET['sort_order']) ? $_GET['sort_order'] : 'date_desc', 'date_asc'); ?>>Oldest First</option>
          <option value="title_asc" <?php selected(isset($_GET['sort_order']) ? $_GET['sort_order'] : 'date_desc', 'title_asc'); ?>>Title (A-Z)</option>
          <option value="title_desc" <?php selected(isset($_GET['sort_order']) ? $_GET['sort_order'] : 'date_desc', 'title_desc'); ?>>Title (Z-A)</option>
        </select>
      </div>
  </div>
</form>
