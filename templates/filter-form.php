<?php
if (!defined('ABSPATH')) { exit; } // Prevent direct access

$selected_taxonomies = get_option('content_filter_taxonomies', []); // Get selected taxonomies
?>

<form class="px-4 sm:px-0" id="resource-filter">
  <!-- Search Field - Theme -->
  <div class="search-text">
    <input class="bg-light border-secondary border-2 full-width" type="text" id="search" name="search" placeholder="Search..." value="<?php echo isset($search) ? esc_attr($search) : ''; ?>">
    <button class="bg-primary text-white" type="submit">Search</button>
    <button type="reset" class="bg-danger text-white" id="clear-search">&times;</button>
  </div>

  <div class="search-tax">
    <?php
    foreach ($selected_taxonomies as $taxonomy):
      $terms = get_terms(['taxonomy' => $taxonomy, 'hide_empty' => true]);
      $taxonomy_obj = get_taxonomy($taxonomy);

      if (!empty($terms) && !empty($taxonomy_obj)): ?>
        <div class="custom-dropdown">
          <button type="button" class="dropdown-toggle"><?php echo esc_html($taxonomy_obj->labels->singular_name); ?></button>
          <div class="dropdown-menu">
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
