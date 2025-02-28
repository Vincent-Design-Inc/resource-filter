<?php
if (!defined('ABSPATH')) { exit; } // Prevent direct access

if (!empty($resources)) :
  foreach ($resources as $resource) :
    $postID    = $resource->ID;
    $postTitle = get_the_title($postID);
    $postLink  = get_permalink($postID);
    $terms     = get_the_terms($postID, 'resource_type');
  ?>
    <div class="px-4 sm:px-0">
      <?php if (has_post_thumbnail($postID)) : ?>
        <div class="block bg-[#F3F3F3] h-[32rem]">
          <a href="<?php echo esc_url($postLink); ?>">
            <img src="<?php echo esc_url(get_the_post_thumbnail_url($postID)); ?>" alt="<?php echo esc_attr($postTitle); ?>"
              class="flex justify-center items-center w-full h-full object-contain">
          </a>
        </div>
      <?php endif; ?>

      <p class="text-sm font-thin uppercase">
        <?php echo $terms ? esc_html(strtolower($terms[0]->name)) : ''; ?>
      </p>

      <div class="flex">
        <div>
          <h3 class="text-lg"><a class="" href="<?php echo esc_url($postLink); ?>"><?php echo esc_html($postTitle); ?></a></h3>
        </div>

        <div class="flex items-center ml-auto pr-4">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 ml-2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
          </svg>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
<?php else : ?>
  <p>No resources found.</p>
<?php endif; ?>
