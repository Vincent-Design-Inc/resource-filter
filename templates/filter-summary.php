<?php
if (!defined('ABSPATH')) { exit; } // Prevent direct access
?>

<div id="resource-filter-summary">
  <p><strong>Showing <span id="result-count"><?php echo isset($resTotal) ? esc_html($resTotal) : 0; ?></span> resources</strong></p>
  <p><strong>Filters applied:</strong><br><span id="applied-filters">None</span></p>
</div>
