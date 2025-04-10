<?php
/**
 * Plugin Name: Content Filter
 * Plugin URI: https://github.com/Vincent-Design-Inc/resource-filter
 * Update URI: https://github.com/Vincent-Design-Inc/resource-filter
 * Description: Adds filtering for the content typed by various taxonomies.
 * Version: 1.6.3
 * Author: Keith Solomon
 * Author URI: https://vincentdesign.ca
 */

if (!defined('ABSPATH')) { exit; } // Prevent direct access

require_once plugin_dir_path(__FILE__) . 'includes/template-loader.php';
require_once plugin_dir_path(__FILE__) . 'includes/GitHubUpdater.php';

class ContentFilterPlugin {
  /** Registers the necessary actions and filters for the Content Filter plugin.
   *
   * Hooks include:
   * - admin_menu: Adds the Content Filter settings page to the WordPress admin menu.
   * - wp_enqueue_scripts: Enqueues the necessary scripts and styles for the resource filter.
   * - wp_ajax_filter_resources: Handles AJAX requests for filtering resources.
   * - wp_ajax_nopriv_filter_resources: Handles non-privileged AJAX requests for filtering resources.
   *
   * @since 1.0.0
   */
  public function __construct() {
    add_action('admin_menu', [$this, 'cfAdminMenu']);
    add_shortcode('resource_filter', [$this, 'renderFilterForm']);
    add_action('wp_enqueue_scripts', [$this, 'enqueueScripts']);
    add_action('wp_ajax_filter_resources', [$this, 'filterResources']);
    add_action('wp_ajax_nopriv_filter_resources', [$this, 'filterResources']);
  }

  /** Registers the Content Filter plugin settings page in the WordPress admin menu.
   *
   * The settings page is accessible under the 'Settings' menu, and is only visible to users with the 'manage_options' capability.
   *
   * @since 1.4.0
   */
  public function cfAdminMenu() {
    add_menu_page(
      'Content Filter Settings', // Page title
      'Content Filter', // Menu title
      'manage_options', // Capability
      'content-filter-settings', // Menu slug
      [$this, 'renderAdminPage'], // Callback function
      'dashicons-filter', // Menu icon
      25 // Position
    );
  }

  /** Renders the Content Filter settings page in the WordPress admin dashboard.
   *
   * The page is accessible under the 'Settings' menu, and is only visible to users with the 'manage_options' capability.
   *
   * The page includes a form for selecting the post types and taxonomies to include in the filter, as well as a number field for setting the number of posts to display per page in the filter results.
   *
   * @since 1.4.0
   */
  public function renderAdminPage() {
    if (!current_user_can('manage_options')) {
      return;
    }

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      check_admin_referer('content_filter_settings');

      $postTypes = isset($_POST['post_types']) ? array_map('sanitize_text_field', $_POST['post_types']) : [];
      $taxonomies = isset($_POST['taxonomies']) ? array_map('sanitize_text_field', $_POST['taxonomies']) : [];
      $posts_per_page = isset($_POST['posts_per_page']) ? intval($_POST['posts_per_page']) : 12;
      $homepage_taxonomy = isset($_POST['homepage_taxonomy']) ? sanitize_text_field($_POST['homepage_taxonomy']) : '';

      update_option('content_filter_homepage_taxonomy', $homepage_taxonomy);
      update_option('content_filter_post_types', $postTypes);
      update_option('content_filter_taxonomies', $taxonomies);
      update_option('content_filter_posts_per_page', $posts_per_page);

      echo '<div class="updated"><p>Settings saved!</p></div>';
    }

    // Get saved options
    $postTypes = get_option('content_filter_post_types', []);
    $taxonomies = get_option('content_filter_taxonomies', []);
    $posts_per_page = get_option('content_filter_posts_per_page', 12);
    $homepage_taxonomy = get_option('content_filter_homepage_taxonomy', '');

    // Get all available post types and taxonomies
    $all_post_types = get_post_types(['public' => true], 'objects');
    $all_taxonomies = get_taxonomies(['public' => true], 'objects');
    ?>
    <div class="wrap">
      <h1>Content Filter Settings</h1>
      <form method="post">
        <?php wp_nonce_field('content_filter_settings'); ?>

        <h2>Post Types</h2>
        <p>Select the post types to include in the filter.</p>
        <?php foreach ($all_post_types as $post_type): ?>
          <label>
            <input type="checkbox" name="post_types[]" value="<?php echo esc_attr($post_type->name); ?>" <?php checked(in_array($post_type->name, $postTypes)); ?>>
            <?php echo esc_html($post_type->labels->singular_name); ?>
          </label><br>
        <?php endforeach; ?>

        <h2>Taxonomies</h2>
        <p>Select the taxonomies to include in the filter.</p>
        <?php foreach ($all_taxonomies as $taxonomy): ?>
          <label>
            <input type="checkbox" name="taxonomies[]" value="<?php echo esc_attr($taxonomy->name); ?>" <?php checked(in_array($taxonomy->name, $taxonomies)); ?>>
            <?php echo esc_html($taxonomy->labels->singular_name); ?>
          </label><br>
        <?php endforeach; ?>

        <h2>Posts Per Page</h2>
        <p>Set the number of posts to display per page in the filter results.</p>
        <input type="number" name="posts_per_page" value="<?php echo esc_attr($posts_per_page); ?>" min="1" step="1">

        <h2>Homepage Taxonomy</h2>
        <p>Select one taxonomy to include in the homepage search form.</p>
        <select name="homepage_taxonomy">
          <option value="">-- Select Taxonomy --</option>
          <?php
          foreach ($all_taxonomies as $taxonomy) : ?>
            <option value="<?php echo esc_attr($taxonomy->name); ?>" <?php selected($taxonomy->name, $homepage_taxonomy); ?>>
              <?php echo esc_html($taxonomy->labels->singular_name); ?>
            </option>
          <?php endforeach; ?>
        </select>

        <p style="margin-top: 1.5rem;"><input type="submit" class="button-primary" value="Save Settings"></p>
      </form>
    </div>
    <?php
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
    // Check if a custom stylesheet exists in the theme directory
    $theme_stylesheet = get_stylesheet_directory() . '/resource-filter/style.css';
    $theme_stylesheet_url = get_stylesheet_directory_uri() . '/resource-filter/style.css';

    if (file_exists($theme_stylesheet)) {
      // Enqueue the stylesheet from the theme
      wp_enqueue_style('content-filter-style', $theme_stylesheet_url, [], filemtime($theme_stylesheet));
    } else {
      // Fall back to the plugin's stylesheet
      wp_enqueue_style('content-filter-style', plugins_url('templates/style.css', __FILE__), [], filemtime(plugin_dir_path(__FILE__) . 'templates/style.css'));
    }

    // Load script only if the shortcode is present on the page
    if (!is_admin() && has_shortcode(get_post_field('post_content', get_the_ID()), 'resource_filter')) {
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

    $query = $this->getQuery();

    global $postsTotal;
    $postsTotal = $query->found_posts;

    ob_start();

    $attTmpl = $this->getTemplate($atts['type']);
    if (!$attTmpl) {
      return ob_get_clean();
    }

    $resForm = rfGetTemplate($attTmpl);
    $summary = rfGetTemplate('filter-summary.php');

    $this->includeTemplate($resForm, 'Form template not found.');

    if ($atts['type'] === 'default') {
      $this->includeTemplate($summary, 'Summary template not found.');
      $this->renderResourceResults($query);
    }

    return ob_get_clean();
  }

  /** Retrieves the template file for the given filter type.
   *
   * @param string $type The type of filter. Accepts 'default' or 'homepage'.
   * @return string|false The path to the template file or false if the homepage filter has no configured taxonomy.
   *
   * @since 1.0.0
   */
  private function getTemplate($type) {
    if ($type === 'homepage') {
      $homepage_taxonomy = get_option('content_filter_homepage_taxonomy', '');

      if (!empty($homepage_taxonomy) && taxonomy_exists($homepage_taxonomy)) {
        $GLOBALS['homepage_taxonomy'] = $homepage_taxonomy;
        return 'filter-homepage.php';
      } else {
        echo '<p>Error: No valid taxonomy configured for the homepage filter.</p>';
        return false;
      }
    } else {
      return 'filter-form.php';
    }
  }

  /** Includes a template file.
   *
   * If the template file is found, it is included once. Otherwise, an error message is displayed.
   *
   * @param string $template The path to the template file.
   * @param string $errorMessage The error message to display if the template file is not found.
   *
   * @since 1.0.0
   */
  private function includeTemplate($template, $errorMessage) {
    if ($template) {
      include_once $template;
    } else {
      echo '<p>Error: ' . $errorMessage . '</p>';
    }
  }

  /** Renders the resource results.
   *
   * If the request method is POST, this directly renders the filtered results.
   * Otherwise, it loads all resources initially.
   *
   * This function also handles pagination for the resource results.
   *
   * @param \WP_Query $query The WP_Query object for the resource results.
   *
   * @since 1.0.0
   */
  private function renderResourceResults($query) {
    ?>
    <div id="resource-results">
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
          $this->filterResources(); // Directly render filtered results
        } else {
          $this->loadResources(); // Load all resources initially
        }

        $pagination_links = paginate_links([
          'total'     => $query->max_num_pages,
          'current'   => max(1, get_query_var('paged', 1)),
          'format'    => '?paged=%#%',
          'prev_text' => '&laquo;',
          'next_text' => '&raquo;',
          'type'      => 'list'
        ]);
        ?>
    </div>
    <?php
    if ($pagination_links) {
      echo '<div class="pagination">' . $pagination_links . '</div>';
    }
  }

  /** Generate a WP_Query object based on the request method.
   *
   * If the request method is POST, build the query args based on the
   * $_POST data. Otherwise, build a basic query for all resources.
   *
   * @return WP_Query The query object
   */
  private function getQuery() {
    $postTypes = get_option('content_filter_post_types', ['post']); // Get selected post types from admin
    $postCount = get_option('content_filter_posts_per_page', 12); // Get posts per page from admin
    $strSearch = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $sort_order = isset($_POST['sort_order']) ? sanitize_text_field($_POST['sort_order']) : 'date_desc';

      $query_args = [
        'post_type'      => $postTypes,
        'posts_per_page' => $postCount,
        'paged'          => max(1, get_query_var('paged', 1)), // Get current page number
        'tax_query'      => $this->buildDynamicTaxQuery(),
        's'              => $strSearch,
      ];

      // Sorting logic
      $query_args = $this->applySorting($query_args, $sort_order);

      $query = new WP_Query($query_args);
      $query->set('count', $query->found_posts);

      return $query;
    } else {
      $query = new WP_Query([
        'post_type'      => $postTypes,
        'posts_per_page' => $postCount,
        'paged'          => max(1, get_query_var('paged', 1)), // Get current page number
        'tax_query'      => $this->buildDynamicTaxQuery(),
        's'              => $strSearch,
      ]);

      $query->set('count', $query->found_posts);

      return $query;
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
        $query_args['order']   = 'ASC';
        break;

      case 'date_desc':
        $query_args['orderby'] = 'date';
        $query_args['order']   = 'DESC';
        break;

      case 'title_asc':
        $query_args['orderby'] = 'title';
        $query_args['order']   = 'ASC';
        break;

      case 'title_desc':
        $query_args['orderby'] = 'title';
        $query_args['order']   = 'DESC';
        break;

      default:
        $query_args['orderby'] = 'date';
        $query_args['order']   = 'DESC';
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
      'post_type'      => get_option('content_filter_post_types', ['post']),
      'posts_per_page' => get_option('content_filter_posts_per_page', 12),
      'paged'          => max(1, get_query_var('paged', 1)), // Get current page number
      'tax_query'      => $this->buildDynamicTaxQuery(),
    ];

    $query = new WP_Query($query_args);
    $resources['count'] = $query->found_posts;
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
    $postTypes = get_option('content_filter_post_types', []);
    $postCount = get_option('content_filter_posts_per_page', 12); // Get posts per page from admin
    $strSearch = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
    $sort_order = isset($_POST['sort_order']) ? sanitize_text_field($_POST['sort_order']) : 'date_desc';

    $query_args = [
      'post_type'      => !empty($postTypes) ? $postTypes : ['post'],
      'posts_per_page' => $postCount,
      'paged'          => isset($_POST['paged']) ? intval($_POST['paged']) : 1, // Get current page number
      'tax_query'      => $this->buildDynamicTaxQuery(),
      's'              => $strSearch,
    ];

    $query_args = $this->applySorting($query_args, $sort_order);
    $query_args['tax_query'] = $this->buildDynamicTaxQuery();

    return $query_args;
  }

  /** Generate a dynamic tax query based on the $_POST data.
   *
   * Looks through the taxonomies specified in the settings and builds a tax query
   * for each one that has a value in the $_POST data. Sanitizes the input data to
   * prevent SQL injection attacks.
   *
   * @return array The tax query array.
   *
   * @since 1.4.0
   */
  private function buildDynamicTaxQuery() {
    $taxonomies = get_option('content_filter_taxonomies', []); // Get selected taxonomies from admin
    $tax_query = [];

    if (!empty($taxonomies)) {
      foreach ($taxonomies as $taxonomy) {
        if (!empty($_POST[$taxonomy])) {
          $terms = is_array($_POST[$taxonomy]) ? array_map('sanitize_text_field', $_POST[$taxonomy]) : sanitize_text_field($_POST[$taxonomy]);

          $tax_query[] = [
            'taxonomy' => $taxonomy,
            'field'    => 'slug',
            'terms'    => $terms,
            'operator' => 'IN',
          ];
        }
      }
    }

    if (!empty($tax_query)) {
      return [
        'relation' => 'AND',
        ...$tax_query,
      ];
    }

    return [];
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
      'count'       => $query->found_posts,
      'filters'     => [
        'search'    => isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '',
      ],
      'html'        => ob_get_clean(),
      'pagination'  => paginate_links([
        'total'     => $query->max_num_pages,
        'current'   => isset($_POST['paged']) ? intval($_POST['paged']) : 1,
        'format'    => '?paged=%#%',
        'add_args'  => [], // Pass additional query arguments
        'prev_text' => '&laquo;',
        'next_text' => '&raquo;',
        'type'      => 'array',
      ])
    ];

    // Include dynamic taxonomy filters in the response
    $taxonomies = get_option('content_filter_taxonomies', []);

    foreach ($taxonomies as $taxonomy) {
      if (!empty($_POST[$taxonomy])) {
        $response['filters'][$taxonomy] = array_map('sanitize_text_field', (array) $_POST[$taxonomy]);
      }
    }

    echo json_encode($response);
    wp_die();
  }
}

new ContentFilterPlugin();
$gitHubUpdater = new GitHubUpdater(plugin_dir_path(__FILE__).'resource-filter.php');
$gitHubUpdater->setChangelog('CHANGELOG.md');
$gitHubUpdater->setPluginIcon('assets/icon-256x256.png');
$gitHubUpdater->setPluginBannerLarge('assets/banner.jpg');
$gitHubUpdater->setPluginBannerSmall('assets/banner.jpg');
$gitHubUpdater->add();
