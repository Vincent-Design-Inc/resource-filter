# Content Filter Plugin

## Description
The Content Filter Plugin adds filtering capabilities for any registered post type by any taxonomies attched to it. This plugin provides a shortcode to display a filter form and dynamically updates the resource results based on the selected filters.

## Installation
1. Download the plugin files and place them in the `wp-content/plugins/resource-filter` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.

## Usage
1. Add the `[resource_filter]` shortcode to any post or page where you want to display the resource filter form.
2. The filter form will allow users to search and filter resources by type and subject.

## Template Override
To override the default form and results templates, create a `resource-filter` directory in your theme and copy the `filter-form.php` and `resource-results.php` files from the plugin `templates` directory to your theme directory. The plugin will use the template files in your theme directory instead of the default template files.

## Changelog
### 1.2.0 - 2025-02-05
- Name change to reflect ability to filter more than 'resources'
- Add secondary search template

### 1.1.0 - 2025-02-05
- Fully templated for use in any theme
- Added result sorting

### 1.0.0 - 2025-02-04
- Initial release
