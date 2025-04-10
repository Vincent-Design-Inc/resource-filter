# Content Filter Plugin

The Content Filter Plugin is a WordPress plugin designed to enhance content discoverability by allowing users to filter resources based on custom taxonomies such as resource type and subject. It provides a user-friendly interface for filtering and sorting resources, making it easier for visitors to find the content they need.

## Features

- **Customizable Filter Form**: Allows filtering by resource type, subject, and search terms.
- **AJAX-Powered Filtering**: Provides a seamless user experience with instant filtering without page reloads.
- **Responsive Design**: Ensures the filter form and results look great on all devices.
- **Template Overrides**: Supports custom templates via theme overrides.
- **Sorting Options**: Enables sorting by date (ascending/descending) and title (ascending/descending).
- **Summary Display**: Shows the number of resources and applied filters dynamically.

## Installation

1. Clone this repo or download the [latest release](https://github.com/Vincent-Design-Inc/resource-filter/releases) and place them in the `wp-content/plugins/resource-filter` directory.
2. Log in to your WordPress admin dashboard.
3. Navigate to **Plugins** > **Installed Plugins**.
4. Activate the **Content Filter** plugin.

## Usage

To use the plugin, add the `[resource_filter]` shortcode to any page or post where you want the filter form to appear. The plugin supports two types of forms:

- **Default Form**: Use `[resource_filter]` for the full filter form with search, resource type, and subject filters.
- **Homepage/Secondary Form**: Use `[resource_filter type="homepage"]` for a simplified form suitable for the homepage.

### Example

Add one of the following shortcodes to your page or post content where you want the filter form to appear:

`[resource_filter]` or `[resource_filter type="homepage"]`

## Configuration

The plugin is designed to work out of the box with minimal configuration. However, you can customize the following:

- **Templates**: Override the default templates and styles by placing your custom versions in your theme's `resource-filter` directory.  You will need to add `"./resource-filter/**/*.{php,css}",` to the `content` object in your `tailwind-config.js` or `@source "./resource-filter/**/*.{php,css}` in your main css file is using Tailwind 4 to compile the custom styles.

  - **Template Files**:
  - `filter-form.php` - Main form template
  - `filter-homepage.php` - Secondary form template for the homepage or other uses
  - `filter-summary.php` - Templae for the summary of the number of resources and applied filters
  - `resource-results.php` - Template for the search results
  - `style.css` - Custom styles for the filter system

## Changelog

## 1.6.3 - 2025-04-10

- Update dropdowns to close when focus is removed

## 1.6.2 - 2025-04-10

- Fixed accessibility issues in the form reset buttons and dropdown modals

## 1.6.1 - 2025-04-07

- Fixed bug in filter removal functionality
- Updated templates for a better starting point for customization

## 1.6.0 - 2025-03-29

- Implement full admin configuarability
- Update action to build releases automatically
- Clean up unneeded files
- Update templates to use new admin settings

## 1.5.4 - 2025-03-03

- Fix count issue
- Re-add filter removal functionality
- Fix bug in search field

### 1.5.3 - 2025-02-28

- Live filtering for type and subject

### 1.5.2 - 2025-02-13

- Bugfix for repo update feature

### 1.5.1 - 2025-02-13

- Fixed bug in filter removal functionality

### 1.5.0 - 2025-02-13

- Added repo update feature

### 1.4.0 - 2025-02-13

- Added admin configuration page

### 1.3.0 - 2025-02-12

- Feature-complete for initial release
- Add pagination
- Add general reset button
- Add tag close functionality

### 1.2.0 - 2025-02-05

- Name change to reflect future ability to filter any content, not just `resources`
- Add secondary search template

### 1.1.0 - 2025-02-05

- Fully templated for use in any theme
- Added result sorting

### 1.0.0 - 2025-02-04

- Initial release

## License

This plugin is licensed under the GNU General Public License v2 or later. See the [LICENSE](LICENSE) file for more details.

## Acknowledgements

- **Author**: Keith Solomon
- **Contributors**: Open to contributions from the community.

## Contribution Guidelines

We welcome contributions! If you'd like to contribute to the Content Filter Plugin, please follow these steps:

1. Fork the repository.
2. Create a new branch for your feature or bug fix.
3. Submit a pull request with a detailed description of your changes.

---

For any issues or feature requests, please open an issue on the [GitHub repository](https://github.com/Vincent-Design-Inc/resource-filter).
