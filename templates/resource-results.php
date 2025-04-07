<?php
if (!defined('ABSPATH')) { exit; } // Prevent direct access

// Define dynamic post type and taxonomy
$postType = isset($postType) ? $postType : 'post'; // Default to 'post' if not set
$taxonomy = isset($taxonomy) ? $taxonomy : 'category'; // Default to 'category' if not set

if (!empty($resources)) :
  foreach ($resources as $resource) :
    $postID    = $resource->ID;
    $postTitle = get_the_title($postID);
    $postLink  = get_permalink($postID);
    $postType  = get_post_type($postID);
    $terms     = get_the_terms($postID, $taxonomy);

    $img = has_post_thumbnail($postID) ? get_the_post_thumbnail_url($postID, 'full') : get_field('admin', 'option')['imgDefault']['url'] ?? '';
  ?>
    <div class="px-4 sm:px-0">
      <div class="block mb-4">
        <a href="<?php echo esc_url($postLink); ?>">
          <img src="<?php echo esc_url($img); ?>" alt="<?php echo esc_attr($postTitle); ?>" class="flex justify-center items-center w-full h-full object-contain">
        </a>
      </div>

      <p class="text-sm font-thin uppercase px-2 mb-1!">
        Post Type: <?php echo $postType; ?>
      </p>

      <div class="flex px-2">
        <div>
          <h3 class="text-lg"><a class="" href="<?php echo esc_url($postLink); ?>"><?php echo esc_html($postTitle); ?></a></h3>
        </div>

        <div class="flex ml-auto mt-1">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 ml-2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
          </svg>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
<?php else : ?>
  <p>No results.</p>
<?php endif; ?>
