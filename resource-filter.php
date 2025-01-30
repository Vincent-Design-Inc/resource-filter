<?php
/**
 * Plugin Name: Resource Filter
 * Description: Adds filtering for the 'resource' post type by 'resource_type' and 'resource_subject'.
 * Version: 1.0
 * Author: Keith Solomon
 */

if (!defined('ABSPATH')) { exit; } // Prevent direct access

class ResourceFilterPlugin {
  public function __construct() {
    add_shortcode('resource_filter', [$this, 'renderFilterForm']);
    add_action('wp_enqueue_scripts', [$this, 'enqueueScripts']);
    add_action('wp_ajax_filter_resources', [$this, 'filterResources']);
    add_action('wp_ajax_nopriv_filter_resources', [$this, 'filterResources']);
  }

  public function enqueueScripts() {
    // Load script only if the shortcode is present on the page
    if (!is_admin() && has_shortcode(get_post_field('post_content', get_the_ID()), 'resource_filter')) {
      wp_enqueue_style('resource-filter-style', plugins_url('assets/style.css', __FILE__));
      wp_enqueue_script('resource-filter-script', plugins_url('assets/script.js', __FILE__), ['jquery'], null, true);

      wp_localize_script('resource-filter-script', 'resourceFilterAjax', [
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('resource_filter_nonce')
      ]);
    }
  }

  public function renderFilterForm() {
    ob_start();
    ?>
    <form id="resource-filter">
      <input type="text" id="search" name="search" placeholder="Search resources...">

      <?php
      $types = get_terms(['taxonomy' => 'resource_type', 'hide_empty' => true]);
      $subjects = get_terms(['taxonomy' => 'resource_subject', 'hide_empty' => true]);
      ?>

      <select id="resource_type" name="resource_type">
        <option value="">All Types</option>
        <?php foreach ($types as $type) : ?>
          <option value="<?php echo esc_attr($type->slug); ?>"><?php echo esc_html($type->name); ?></option>
        <?php endforeach; ?>
      </select>

      <select id="resource_subject" name="resource_subject">
        <option value="">All Subjects</option>
        <?php foreach ($subjects as $subject) : ?>
          <option value="<?php echo esc_attr($subject->slug); ?>"><?php echo esc_html($subject->name); ?></option>
        <?php endforeach; ?>
      </select>

      <button type="submit">Filter</button>
    </form>

    <!-- Filter summary section -->
    <div id="resource-filter-summary">
      <strong>Showing <span id="result-count">0</span> resources</strong>
      <p>Filters applied: <span id="applied-filters">None</span></p>
    </div>

    <div id="resource-results">
      <?php $this->loadResources(); ?>
    </div>

    <?php
    return ob_get_clean();
  }

  public function filterResources() {
    check_ajax_referer('resource_filter_nonce', 'nonce');

    // $query_args = [
    //   'post_type' => 'resource',
    //   'posts_per_page' => -1,
    //   'tax_query' => [],
    //   's' => isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '',
    // ];

    $search_term = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';

    $query_args = [
      'post_type' => 'resource',
      'posts_per_page' => -1,
      'tax_query' => ['relation' => 'OR'], // Allows matching by search OR taxonomy
      'meta_query' => [
        'relation' => 'OR',
        [
          'key' => 'post_title',
          'value' => $search_term,
          'compare' => 'LIKE'
        ],
        [
          'key' => 'post_content',
          'value' => $search_term,
          'compare' => 'LIKE'
        ]
      ]
    ];

    if (!empty($search_term)) {
      $query_args['tax_query'][] = [
        'taxonomy' => 'resource_type',
        'field' => 'name', // Search by the name of the taxonomy term
        'terms' => $search_term,
        'operator' => 'LIKE'
      ];

      $query_args['tax_query'][] = [
        'taxonomy' => 'resource_subject',
        'field' => 'name',
        'terms' => $search_term,
        'operator' => 'LIKE'
      ];
    }

    if (!empty($_POST['resource_type'])) {
      $query_args['tax_query'][] = [
        'taxonomy' => 'resource_type',
        'field' => 'slug',
        'terms' => sanitize_text_field($_POST['resource_type'])
      ];
    }

    if (!empty($_POST['resource_subject'])) {
      $query_args['tax_query'][] = [
        'taxonomy' => 'resource_subject',
        'field' => 'slug',
        'terms' => sanitize_text_field($_POST['resource_subject'])
      ];
    }

    if (!empty($_POST['resource_type']) || !empty($_POST['resource_subject'])) {
      $query_args['tax_query']['relation'] = 'AND';

      if (!empty($_POST['resource_type'])) {
        $query_args['tax_query'][] = [
          'taxonomy' => 'resource_type',
          'field' => 'slug',
          'terms' => sanitize_text_field($_POST['resource_type'])
        ];
      }

      if (!empty($_POST['resource_subject'])) {
        $query_args['tax_query'][] = [
          'taxonomy' => 'resource_subject',
          'field' => 'slug',
          'terms' => sanitize_text_field($_POST['resource_subject'])
        ];
      }
    }

    $query = new WP_Query($query_args);
    ob_start();

    if ($query->have_posts()) {
      echo '<div class="resource-grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">';
      while ($query->have_posts()) {
        $query->the_post();
        echo '<div class="resource-item"><a href="' . get_permalink() . '">' . get_the_title() . '</a></div>';
      }
      echo '</div>';
    } else {
      echo '<p>No resources found.</p>';
    }

    print_r($query_args);

    wp_reset_postdata();

    // Prepare response JSON
    $response = [
      'count' => $query->found_posts,
      'filters' => [
        'search' => isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '',
        'resource_type' => !empty($_POST['resource_type']) ? sanitize_text_field($_POST['resource_type']) : '',
        'resource_subject' => !empty($_POST['resource_subject']) ? sanitize_text_field($_POST['resource_subject']) : ''
      ],
      'html' => ob_get_clean()
    ];

    echo json_encode($response);
    wp_die();
  }

  private function loadResources($query_args = ['post_type' => 'resource', 'posts_per_page' => -1]) {
    $query = new WP_Query($query_args);

    if ($query->have_posts()) {
      echo '<div class="resource-grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">';
      while ($query->have_posts()) {
        $query->the_post();
        echo '<div class="resource-item"><a href="' . get_permalink() . '">' . get_the_title() . '</a></div>';
      }
      echo '</div>';
    } else {
      echo '<p>No resources found.</p>';
    }

    wp_reset_postdata();
  }
}

new ResourceFilterPlugin();
