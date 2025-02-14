<?php
if (!defined('ABSPATH')) { exit; } // Prevent direct access

$homepage_taxonomy = isset($GLOBALS['homepage_taxonomy']) ? $GLOBALS['homepage_taxonomy'] : '';

if (!empty($homepage_taxonomy)) :
  $taxonomy_obj = get_taxonomy($homepage_taxonomy);
  $terms = get_terms(['taxonomy' => $homepage_taxonomy, 'hide_empty' => true]);

  if (!empty($taxonomy_obj) && !empty($terms)) : ?>
    <form id="homepage-filter">
      <!-- Search Field -->
      <div class="search-text">
        <input class="full-width" type="text" id="search" name="search" placeholder="Search resources..." value="<?php echo isset($search) ? esc_attr($search) : ''; ?>">
        <button type="reset" id="clear-search">&times;</button>
        <button type="submit">Search</button>
      </div>

      <!-- Single Taxonomy Filter -->
      <div class="search-tax">
        <details class="taxonomy-filter" data-taxonomy="<?php echo esc_attr($homepage_taxonomy); ?>">
          <summary><?php echo esc_html($taxonomy_obj->labels->singular_name); ?></summary>
          <div class="filter-options">
            <?php foreach ($terms as $term) : ?>
              <label>
                <input type="checkbox" name="<?php echo esc_attr($homepage_taxonomy); ?>[]" value="<?php echo esc_attr($term->slug); ?>">
                <?php echo esc_html($term->name); ?>
              </label>
            <?php endforeach; ?>
          </div>
        </details>
      </div>
    </form>
  <?php endif; ?>
<?php endif; ?>
