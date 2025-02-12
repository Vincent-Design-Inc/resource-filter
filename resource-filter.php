<?php
/**
 * Plugin Name: Content Filter
 * Description: Adds filtering for the content typed by various taxonomies.
 * Version: 1.2.0
 * Author: Keith Solomon
 */

if (!defined('ABSPATH')) { exit; } // Prevent direct access

require_once plugin_dir_path(__FILE__) . 'includes/template-loader.php';

class ResourceFilterPlugin {
  /** Registers the necessary actions and filters.
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

  /** Enqueues the necessary scripts and styles for the resource filter.
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

  /** Renders the resource filter form.
   *
   * Loads the filter form template and displays a summary of the total resources.
   * Displays the number of resources and applied filters.
   * Calls the loadResources method to display the initial list of resources.
   *
   * @return string The HTML output of the filter form and resource list.
   *
   * @since 1.0.0
   */
  public function renderFilterForm($atts) {
    $atts = shortcode_atts([
      'type' => 'default' // Accepts 'default' or 'homepage'
    ], $atts, 'resource_filter');

    ob_start();

    $query = $this->getQuery();

    $resTotal = $query->found_posts; // Default total resource count

    // Determine which form template to load
    $attTmpl = ($atts['type'] === 'homepage') ? 'filter-homepage.php' : 'filter-form.php';
    $resForm = rfGetTemplate($attTmpl);
    $summary = rfGetTemplate('filter-summary.php');

    if ($resForm) {
      include_once $resForm;
    } else {
      echo '<p>Error: Form template not found.</p>';
    }

    if ($atts['type'] === 'default') {
      if ($summary) {
        include_once $summary;
      } else {
        echo '<p>Error: Summary template not found.</p>';
      }
    ?>

    <div id="resource-results">
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
          $this->filterResources(); // Directly render filtered results
        } else {
          $this->loadResources(); // Load all resources initially
        }

        $pagination_links = paginate_links([
          'total' => $query->max_num_pages,
          'current' => max(1, get_query_var('paged', 1)),
          'format' => '?paged=%#%',
          'prev_text' => '&laquo;',
          'next_text' => '&raquo;',
          'type' => 'list'
        ]);
        ?>
    </div>
    <?php
      if ($pagination_links) {
        echo '<div class="pagination">' . $pagination_links . '</div>';
      }
    }

    return ob_get_clean();
  }

  /** Generate a WP_Query object based on the request method.
   *
   * If the request method is POST, build the query args based on the
   * $_POST data. Otherwise, build a basic query for all resources.
   *
   * @return WP_Query The query object
   */
  private function getQuery() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $sort_order = isset($_POST['sort_order']) ? sanitize_text_field($_POST['sort_order']) : 'date_desc';

      $query_args = [
        'post_type'      => 'resource',
        'posts_per_page' => 12, // Show 12 results per page
        'paged' => max(1, get_query_var('paged', 1)), // Get current page number
        'tax_query'      => [],
        's'              => isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '',
      ];

      // Sorting logic
      $query_args = $this->applySorting($query_args, $sort_order);

      $tax_query = [];

      if (!empty($_POST['resource_type'])) {
        $resType = is_array($_POST['resource_type']) ? array_map('sanitize_text_field', $_POST['resource_type']) : sanitize_text_field($_POST['resource_type']);

        $query_args['tax_query'][] = [
          'taxonomy' => 'resource_type',
          'field'    => 'slug',
          'terms'    => $resType,
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

      return new WP_Query($query_args);
    } else {
      return new WP_Query([
        'post_type' => 'resource',
        'posts_per_page' => 12, // Show 12 results per page
        'paged' => max(1, get_query_var('paged', 1)), // Get current page number
      ]);
    }
  }

  /** Modify the query args based on the specified sort order.
   *
   * The sort order can be one of the following:
   * - date_asc: Sort by date in ascending order (oldest first).
   * - date_desc: Sort by date in descending order (newest first).
   * - title_asc: Sort by title in ascending order (A-Z).
   * - title_desc: Sort by title in descending order (Z-A).
   *
   * If an invalid sort order is provided, the query args will default to sorting
   * by date in descending order (newest first).
   *
   * @param array $query_args The query args to modify.
   * @param string $sort_order The desired sort order.
   *
   * @return array The modified query args.
   *
   * @since 1.0.0
   */
  private function applySorting($query_args, $sort_order) {
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

    return $query_args;
  }

  /** Load and display resources.
   *
   * @return void
   *
   * @since 1.0.0
   */
  public function loadResources() {
    $query_args = [
      'post_type' => 'resource',
      'posts_per_page' => 12, // Show 12 results per page
      'paged' => max(1, get_query_var('paged', 1)), // Get current page number
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

  /** AJAX handler for filtering resources.
   *
   * Searches for resources based on search term, resource type, and/or resource subject.
   * Returns a JSON response with the count of resources found and the HTML for the resource results.
   *
   * Verifies the nonce and sanitizes the input data.
   *
   * @since 1.0.0
   */
  public function filterResources() {
    $is_ajax = defined('DOING_AJAX') && DOING_AJAX;

    if ($is_ajax) {
      check_ajax_referer('resource_filter_nonce', 'nonce');
    }

    $query_args = $this->buildQueryArgs();

    $query = new WP_Query($query_args);

    ob_start();

    $resources = $query->posts;

    $resResults = rfGetTemplate('resource-results.php');

    if ($resResults) {
      include_once $resResults;
    } else {
      echo '<p>Error: Results template not found.</p>';
    }

    if ($is_ajax) {
      $this->sendAjaxResponse($query);
    } else {
      echo ob_get_clean();
    }
  }

  /** Build the query arguments array for filtering resources.
   *
   * Uses the $_POST data to generate the query arguments for the WP_Query object.
   * Sanitizes the input data to prevent SQL injection attacks.
   *
   * @return array The query arguments array.
   *
   * @since 1.0.0
   */
  private function buildQueryArgs() {
    $sort_order = isset($_POST['sort_order']) ? sanitize_text_field($_POST['sort_order']) : 'date_desc';

    $query_args = [
      'post_type'      => 'resource',
      'posts_per_page' => 12, // Show 12 results per page
      'paged'          => isset($_POST['paged']) ? intval($_POST['paged']) : 1, // Get current page number
      'tax_query'      => [],
      's'              => isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '',
    ];

    $query_args = $this->applySorting($query_args, $sort_order);
    $query_args['tax_query'] = $this->buildTaxQuery();

    return $query_args;
  }

  /** Builds a taxonomy query array for filtering resources by type and subject.
   *
   * Constructs a taxonomy query based on the 'resource_type' and 'resource_subject'
   * POST parameters. The function checks if these parameters are present and
   * properly sanitizes them before adding them to the query. If both parameters
   * are provided, the query will require both conditions to match using an 'AND'
   * relation.
   *
   * @return array The constructed tax query array, or an empty array if no filters are applied.
   */

  private function buildTaxQuery() {
    $tax_query = [];

    if (!empty($_POST['resource_type'])) {
      $resType = is_array($_POST['resource_type']) ? array_map('sanitize_text_field', $_POST['resource_type']) : sanitize_text_field($_POST['resource_type']);

      $tax_query[] = [
        'taxonomy' => 'resource_type',
        'field'    => 'slug',
        'terms'    => $resType,
        'operator' => 'IN'
      ];
    }

    if (!empty($_POST['resource_subject'])) {
      $tax_query[] = [
        'taxonomy' => 'resource_subject',
        'field'    => 'slug',
        'terms'    => array_map('sanitize_text_field', $_POST['resource_subject']),
        'operator' => 'IN'
      ];
    }

    if (!empty($tax_query)) {
      return [
        'relation' => 'AND', // Both filters must match
        ...$tax_query
      ];
    }

    return $tax_query;
  }

  /** Sends an AJAX response with resource filtering results.
   *
   * Constructs a response array containing the number of resources found,
   * the applied filters, the HTML output of the resources, and pagination links.
   * The response is then encoded as a JSON object and sent back to the client.
   *
   * @param WP_Query $query The query object containing the filtered resources.
   *
   * @since 1.0.0
   */

  private function sendAjaxResponse($query) {
    $response = [
      'count' => $query->found_posts,
      'filters' => [
        'search' => isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '',
        'resource_type' => !empty($_POST['resource_type']) ? sanitize_text_field($_POST['resource_type']) : '',
        'resource_subject' => !empty($_POST['resource_subject']) ? sanitize_text_field($_POST['resource_subject']) : ''
      ],
      'html' => ob_get_clean(),
      'pagination' => paginate_links([
        'total' => $query->max_num_pages,
        'current' => isset($_POST['paged']) ? intval($_POST['paged']) : 1,
        'format' => '?paged=%#%',
        'add_args' => [], // Pass additional query arguments
        'prev_text' => '&laquo;',
        'next_text' => '&raquo;',
        'type' => 'array',
      ])
    ];

    echo json_encode($response);
    wp_die();
  }
}

new ResourceFilterPlugin();
