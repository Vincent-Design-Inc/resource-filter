<?php
if (!defined('ABSPATH')) { exit; } // Prevent direct access

$selected_taxonomies = get_option('content_filter_taxonomies', []); // Get selected taxonomies
?>

<form class="px-4 sm:px-0" id="resource-filter">
  <!-- Search Form - Plugin -->
  <div class="search-text">
    <div class="search-input-wrapper flex-grow">
      <input class="full-width bg-[#F8F8F8] border-[#8B8B8B] border rounded-xl" type="text" id="search" name="search"
        placeholder="Search resources..." value="<?php echo isset($search) ? esc_attr($search) : ''; ?>">
      <button type="reset" id="clear-search">&times;</button>
    </div>
    <div class="flex justify-center md:justify-start">
      <button class="btn btn-primary bg-primary text-white rounded-xl w-full md:w-auto px-4 py-2" type="submit">Search</button>
    </div>
  </div>

  <div class="search-tax">
    <?php
    foreach ($selected_taxonomies as $taxonomy):
      $terms = get_terms(['taxonomy' => $taxonomy, 'hide_empty' => true]);
      $taxonomy_obj = get_taxonomy($taxonomy);

      if (!empty($terms) && !empty($taxonomy_obj)): ?>
        <div class="custom-dropdown">
          <button id="<?php echo esc_attr($taxonomy); ?>_toggle" type="button" class="dropdown-toggle" aria-haspopup="true" aria-expanded="false">
            <div id="<?php echo esc_attr($taxonomy); ?>_text" class="dropdown-text"><?php echo esc_html($taxonomy_obj->labels->singular_name); ?></div>
          </button>
          <div class="dropdown-menu taxonomy-filter" data-taxonomy="<?php echo esc_attr($taxonomy); ?>">
            <?php foreach ($terms as $term): ?>
              <label>
                <input type="checkbox" name="<?php echo esc_attr($taxonomy); ?>[]" value="<?php echo esc_attr($term->slug); ?>" <?php echo (isset($_POST[$taxonomy]) && in_array($term->slug, (array) $_POST[$taxonomy])) ? 'checked' : ''; ?>>
                <?php echo esc_html($term->name); ?>
              </label>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>
    <?php endforeach; ?>
  </div>
</form>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('resource-filter');
    const resetButton = document.getElementById('clear-search');

    resetButton.addEventListener('click', function (e) {
      e.preventDefault(); // Prevent the default reset behavior

      // Clear all input fields
      form.reset();

      // Clear all checkboxes
      const checkboxes = form.querySelectorAll('input[type="checkbox"]');
      checkboxes.forEach(checkbox => checkbox.checked = false);

      // Reset dropdown text to taxonomy name
      document.querySelectorAll('.custom-dropdown').forEach(function (dropdown) {
        const taxonomy = dropdown.querySelector('.dropdown-toggle').id.replace('_toggle', '');
        const taxonomyName = taxonomy.replace(/_/g, ' ').replace(/\b\w/g, char => char.toUpperCase()); // Convert to title case
        dropdown.querySelector('.dropdown-text').textContent = taxonomyName;
      });

      // Trigger filtering without reloading the page
      if (typeof triggerFiltering === 'function') {
        triggerFiltering(1); // Call the function with the first page
      }
    });
  });
</script>
