<?php
if (!defined('ABSPATH')) { exit; } // Prevent direct access

if (!empty($resources)) :
  foreach ($resources as $resource) :
    $postID    = $resource->ID;
    $postTitle = get_the_title($postID);
    $postLink  = get_permalink($postID);
  ?>
    <div class="resource-item border border-primary-500 p-4 rounded">
      <h3 class="text-22px font-semibold leading-2 my-0 py-0"><a class="text-indigo-400" href="<?php echo esc_url($postLink); ?>"><?php echo esc_html($postTitle); ?></a></h3>

      <div class="flex flex-col mt-8">
        <p class="text-14px leading-tight my-0 py-0"><strong>Resource Type:</strong> <?php echo esc_html(get_the_terms($postID, 'resource_type')[0]->name); ?></p>
        <p class="text-14px leading-tight my-0 py-0">
          <strong>Resource Subject(s):</strong>
          <?php
          $subjects = get_the_terms($postID, 'resource_subject');
          $count = count($subjects);
          $i = 1;

          foreach ($subjects as $subject) {
            if ($i === $count) {
              echo esc_html($subject->name);
            } else {
              echo esc_html($subject->name) . ', ';
            }

            $i++;
          }
          ?>
        </p>
      </div>
    </div>
  <?php endforeach; ?>
<?php else : ?>
  <p>No resources found.</p>
<?php endif; ?>
