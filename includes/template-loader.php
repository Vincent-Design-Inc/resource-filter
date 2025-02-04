<?php
if (!defined('ABSPATH')) { exit; } // Prevent direct access

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
