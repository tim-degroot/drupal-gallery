<?php

/**
 * @file
 * Theming for TARDIS views.
 */
function template_preprocess_views_view_displays(&$variables) {
  // View options set by the user.
  $options = $variables['view']->style_plugin->options;

  // Build a two-dimension array with years and months.
  $time_pool = [];

  foreach ($variables['view']->result as $id => $result) {
    // Todo: date field options apart from "created date".
    $created = $result->node_field_data_created;
    $created_year = date('Y', $created);
    // Month date format.
    $month_date_format = (isset($options['month_date_format'])) ? $options['month_date_format'] : 'm';
    $created_month_digits = date('m', $created);
    $created_month = date($month_date_format, $created);
    $time_pool[$created_year][$created_month_digits] = "$created_month";
  }

  $options['time_pool'] = $time_pool;

  // Update options for twig.
  $variables['options'] = $options;
}

?>