<?php
if (!defined('ABSPATH')) { exit; } // Prevent direct access

if (!empty($resources)) : ?>
  <?php
  foreach ($resources as $resource) :
    $post_id    = $resource->ID;
    $post_title = get_the_title($post_id);
    $post_link  = get_permalink($post_id);
  ?>
    <div class="resource-item"><a href="<?php echo esc_url($post_link); ?>"><?php echo esc_html($post_title); ?></a></div>
  <?php endforeach; ?>
<?php else : ?>
  <p>No resources found.</p>
<?php endif; ?>
