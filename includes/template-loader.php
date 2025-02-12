<?php
if (!defined('ABSPATH')) { exit; } // Prevent direct access

/** Retrieves the path to a template file.
 *
 * Checks if a template file exists in the theme's "resource-filter" directory
 * first, and returns its path if found. If not found in the theme directory,
 * it checks the plugin's templates directory. If the template is found in
 * neither location, it returns false.
 *
 * @param string $template_name The name of the template file to retrieve.
 * @return string|false The path to the template file if found, or false if not.
 */

function rfGetTemplate($template_name) {
  $theme_template = get_stylesheet_directory() . "/resource-filter/$template_name";
  $plugin_template = plugin_dir_path(__FILE__) . "../templates/$template_name";

  if (file_exists($theme_template)) {
    return $theme_template;
  } elseif (file_exists($plugin_template)) {
    return $plugin_template;
  }

  return false;
}
