# Content Filter Plugin

## Description
The Content Filter Plugin adds filtering capabilities for any registered post type by any taxonomies attched to it. This plugin provides a shortcode to display a filter form and dynamically updates the resource results based on the selected filters.

## Installation
1. Clone this repo or download the plugin files and place them in the `wp-content/plugins/resource-filter` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.

## Usage
1. Add the `[resource_filter]` shortcode to any post or page where you want to display the resource filter form.
2. For the secondary search form, add the `[resource_filter type="homepage"]` shortcode to any post or page where you want to display the secondary search form.
3. The filter form will allow users to search and filter resources by type, subject and text searching.

## Template Override
To override the default form and results templates, copy the contents of the plugin `templates` directory to the `resource-filter` directory in your theme. The plugin will use the template files in your theme directory instead of the default plugin versions.

### Template Files
- `filter-form.php` - Main form template
- `filter-homepage.php` - Secondary form template for the homepage or other uses
- `filter-summary.php` - Templae for the summary of the number of resources and applied filters
- `resource-results.php` - Template for the search results

## Changelog
### 1.2.0 - 2025-02-05
- Name change to reflect future ability to filter any content, not just `resources`
- Add secondary search template

### 1.1.0 - 2025-02-05
- Fully templated for use in any theme
- Added result sorting

### 1.0.0 - 2025-02-04
- Initial release
