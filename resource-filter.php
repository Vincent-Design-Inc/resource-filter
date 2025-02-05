<?php
/**
 * Plugin Name: Resource Filter
 * Description: Adds filtering for the 'resource' post type by 'resource_type' and 'resource_subject'.
 * Version: 1.1.0
 * Author: Keith Solomon
 */

if (!defined('ABSPATH')) { exit; } // Prevent direct access

require_once plugin_dir_path(__FILE__) . 'includes/template-loader.php';

class ResourceFilterPlugin {
  /**
   * Registers the necessary actions and filters.
   *
   * Adds a shortcode handler for the 'resource_filter' shortcode.
   * Enqueues the necessary scripts and styles.
   * Adds an AJAX handler for filtering resources.
   *
   * @since 1.0.0
   */
  public function __construct() {
    add_shortcode('resource_filter', [$this, 'renderFilterForm']);
    add_action('wp_enqueue_scripts', [$this, 'enqueueScripts']);
    add_action('wp_ajax_filter_resources', [$this, 'filterResources']);
    add_action('wp_ajax_nopriv_filter_resources', [$this, 'filterResources']);
  }

  /**
   * Enqueues the necessary scripts and styles for the resource filter.
   *
   * Checks if the 'resource_filter' shortcode is present on the page before loading.
   * Includes a CSS style and a JavaScript file specific to the plugin.
   * Localizes the script with AJAX URL and nonce for secure AJAX requests.
   *
   * @since 1.0.0
   */
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

  /**
   * Renders the resource filter form.
   *
   * Loads the filter form template and displays a summary of the total resources.
   * Displays the number of resources and applied filters.
   * Calls the loadResources method to display the initial list of resources.
   *
   * @return string The HTML output of the filter form and resource list.
   *
   * @since 1.0.0
   */
  public function renderFilterForm() {
    ob_start();

    $query = new WP_Query([
      'post_type' => 'resource',
      'posts_per_page' => -1,
    ]);

    $resTotal = $query->found_posts; // Get the count of published resources

    $resForm = rfGetTemplate('filter-form.php');
    $summary = rfGetTemplate('filter-summary.php');

    if ($resForm) {
      include_once $resForm;
    } else {
      echo '<p>Error: Form template not found.</p>';
    }

    if ($summary) {
      include_once $summary;
    }else {
      echo '<p>Error: Summary template not found.</p>';
    }
    ?>

    <div id="resource-results">
      <?php $this->loadResources(); ?>
    </div>

    <?php
    return ob_get_clean();
  }

  /**
   * Load and display resources.
   *
   * @return void
   *
   * @since 1.0.0
   */
  public function loadResources() {
    $query_args = [
      'post_type' => 'resource',
      'posts_per_page' => -1
    ];

    $query = new WP_Query($query_args);
    $resources = $query->posts;

    $resResults = rfGetTemplate('resource-results.php');

    if ($resResults) {
      include_once $resResults;
    } else {
      echo '<p>Error: Results template not found.</p>';
    }

    wp_reset_postdata();
  }

  /**
   * AJAX handler for filtering resources.
   *
   * Searches for resources based on search term, resource type, and/or resource subject.
   * Returns a JSON response with the count of resources found and the HTML for the resource results.
   *
   * Verifies the nonce and sanitizes the input data.
   *
   * @since 1.0.0
   */
  public function filterResources() {
    check_ajax_referer('resource_filter_nonce', 'nonce');

    $sort_order = isset($_POST['sort_order']) ? sanitize_text_field($_POST['sort_order']) : 'date_desc';

    $query_args = [
      'post_type'      => 'resource',
      'posts_per_page' => -1,
      'tax_query'      => [],
      's'             => isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '',
    ];

    // Sorting logic
    switch ($sort_order) {
      case 'date_asc':
        $query_args['orderby'] = 'date';
        $query_args['order'] = 'ASC';
        break;

      case 'date_desc':
        $query_args['orderby'] = 'date';
        $query_args['order'] = 'DESC';
        break;

      case 'title_asc':
        $query_args['orderby'] = 'title';
        $query_args['order'] = 'ASC';
        break;

      case 'title_desc':
        $query_args['orderby'] = 'title';
        $query_args['order'] = 'DESC';
        break;

      default:
        $query_args['orderby'] = 'date';
        $query_args['order'] = 'DESC';
    }

    $tax_query = [];

    if (!empty($_POST['resource_type'])) {
      $query_args['tax_query'][] = [
        'taxonomy' => 'resource_type',
        'field'    => 'slug',
        'terms'    => array_map('sanitize_text_field', $_POST['resource_type']),
        'operator' => 'IN'
      ];
    }

    if (!empty($_POST['resource_subject'])) {
      $query_args['tax_query'][] = [
        'taxonomy' => 'resource_subject',
        'field'    => 'slug',
        'terms'    => array_map('sanitize_text_field', $_POST['resource_subject']),
        'operator' => 'IN'
      ];
    }

    if (!empty($tax_query)) {
      $query_args['tax_query'] = [
        'relation' => 'AND', // Both filters must match
        ...$tax_query
      ];
    }

    $query = new WP_Query($query_args);

    ob_start();

    $resources = $query->posts;

    $resResults = rfGetTemplate('resource-results.php');

    if ($resResults) {
      include_once $resResults;
    } else {
      echo '<p>Error: Results template not found.</p>';
    }

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
}

new ResourceFilterPlugin();
