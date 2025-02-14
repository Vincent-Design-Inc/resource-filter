<?php
if (!defined('ABSPATH')) { exit; } // Prevent direct access

// Get configured taxonomies from the admin settings
$configured_taxonomies = get_option('content_filter_taxonomies', []);
?>

<form id="resource-filter">
  <!-- Search Field-->
  <div class="search-text">
    <input class="full-width" type="text" id="search" name="search" placeholder="Search resources..." value="<?php echo isset($search) ? esc_attr($search) : ''; ?>">

    <button type="reset" id="clear-search">&times;</button>
    <button type="submit">Filter</button>
  </div>

  <div class="search-tax">
    <?php if (!empty($configured_taxonomies)) : ?>
      <?php foreach ($configured_taxonomies as $taxonomy) :
        $taxonomy_obj = get_taxonomy($taxonomy);

        if ($taxonomy_obj) :
          $terms = get_terms(['taxonomy' => $taxonomy, 'hide_empty' => true]);

          if (!empty($terms)) : ?>
            <details class="taxonomy-filter" data-taxonomy="<?php echo esc_attr($taxonomy); ?>">
              <summary><?php echo esc_html($taxonomy_obj->labels->singular_name); ?></summary>
              <div class="filter-options">
                <?php foreach ($terms as $term) : ?>
                  <label>
                    <input type="checkbox" name="<?php echo esc_attr($taxonomy); ?>[]" value="<?php echo esc_attr($term->slug); ?>"
                    <?php echo (isset($_POST[$taxonomy]) && in_array($term->slug, (array)$_POST[$taxonomy])) ? 'checked' : ''; ?>>
                    <?php echo esc_html($term->name); ?>
                  </label>
                <?php endforeach; ?>
              </div>
            </details>
          <?php endif; ?>
        <?php endif; ?>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</form>
