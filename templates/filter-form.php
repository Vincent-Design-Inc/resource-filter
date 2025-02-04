<?php
if (!defined('ABSPATH')) { exit; } // Prevent direct access

$resource_types = get_terms(['taxonomy' => 'resource_type', 'hide_empty' => true]);
$resource_subjects = get_terms(['taxonomy' => 'resource_subject', 'hide_empty' => true]);
?>

<form id="resource-filter">
  <input type="text" id="search" name="search" placeholder="Search resources...">

  <select id="resource_type" name="resource_type">
    <option value="">All Types</option>
    <?php foreach ($resource_types as $type) : ?>
      <option value="<?php echo esc_attr($type->slug); ?>"><?php echo esc_html($type->name); ?></option>
    <?php endforeach; ?>
  </select>

  <select id="resource_subject" name="resource_subject">
    <option value="">All Subjects</option>
    <?php foreach ($resource_subjects as $subject) : ?>
      <option value="<?php echo esc_attr($subject->slug); ?>"><?php echo esc_html($subject->name); ?></option>
    <?php endforeach; ?>
  </select>

  <button type="submit">Filter</button>
</form>
