<?php if (!defined('ABSPATH')) { exit; } ?>

<form class="flex w-full" id="homepage-filter" action="<?php echo site_url('/browse-resources/'); ?>" method="POST">
  <?php
  $resource_types = get_terms(['taxonomy' => 'resource_type']);

  if (!empty($resource_types)) :
    ?>
    <select class="w-fit"  name="resource_type">
      <option value="">All Types</option>
      <?php foreach ($resource_types as $type) : ?>
        <option value="<?php echo esc_attr($type->slug); ?>"><?php echo esc_html($type->name); ?></option>
      <?php endforeach; ?>
    </select>
  <?php endif; ?>

  <input class="full-width" type="text" name="search" placeholder="Search resources...">

  <button class="btn btn-primary" type="submit">Search</button>
</form>
