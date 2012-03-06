<?php
/**
 * @file
 * Template overrides and preprocessors for Alternator theme.
 */

/**
 * Implements hook_preprocess_node().
 */
function alternator_preprocess_node(&$vars) {
  global $language;
  $vars['mobile_image_rendered'] = '';

  if (in_array($vars['type'], array('article', 'event'))) {
    unset($vars['links']);
    unset($vars['field_library_ref_rendered']);
    unset($vars['field_list_image_rendered']);
    unset($vars['field_content_images_rendered']);
    unset($vars['field_file_attachments_rendered']);

    // If ding_mobile_images is enabled, use field_mobile_image for
    // images, else use list_image.
    $image_field = module_exists('ding_mobile_images') ? $vars['field_mobile_image'] : $vars['field_list_image'];
    if (!empty($image_field[0]['fid'])) {
      $vars['mobile_image_rendered'] = theme('imagecache', 'mobile_list_image', $image_field[0]['filepath']);
    }

    $vars['submitted'] = format_date($vars['created'], 'large', 'Europe/Copenhagen', $language->language);

    if ($vars['type'] == 'event') {
      $vars['submitted'] = $vars['node']->field_datetime[0]['view'];
      $vars['price'] = $vars['node']->field_entry_price[0]['view'];
    }

    /*
     * 'Unprint' some node elements and rerender. Not really the right
     * way to handle this, but legacy code simply grabbed
     * $node->content['body']['#value'], and redoing it properly would
     * require updating of too many existing sites.
     */
    unset($vars['node']->content['#printed']);
    unset($vars['node']->content['body']['#printed']);
    if (isset($vars['node']->content['place2book_infolink'])) {
      unset($vars['node']->content['place2book_infolink']['#printed']);
    }
    $vars['content'] = drupal_render($vars['node']->content);
  }
}

/**
 * Implements hook_preprocess_page().
 */
function alternator_preprocess_page(&$variables) {
  if (in_array('page-user-login', $variables['template_files'])) {
    $variables['content'] = '<h1>' . t('Login') . '</h1>' . $variables['content'];
  }

  if (in_array('page-user-status', $variables['template_files'])) {
    $variables['content'] = '<h1>' . t('Min konto') . '</h1>' . $variables['content'];
  }

  // Render the main navigation menu.
  $variables['main_menu'] = theme('links', menu_navigation_links('menu-mobile-menu'), array('class' => 'top-menu mobilemenu clear-block'));

  // Get bottom navigation links.
  $bottom_menu = menu_navigation_links('menu-mobile-bottom-menu');

  // Add link to the desktop version.
  $bottom_menu['mainsite'] = array('href' => variable_get('mobile_tools_desktop_url', ''), 'title' => t('Go to the library site'));

  if (!drupal_is_front_page()) {
    $bottom_menu = array_merge(array('frontpage' => array('href' => '<front>', 'title' => t('Front page'))), $bottom_menu);
  }
  $variables['bottom_menu'] = theme('links', $bottom_menu, array('class' => 'bottom-menu mobilemenu clear-block'));

  // Add admin class to the body if applicable.
  if (!empty($variables['admin'])) {
    $variables['body_classes'] .= ' admin';
  }
}

/**
 * Deprecated formatter for danmarc2 data.
 *
 * Remove at earliest convenience.
 */
function format_danmarc2($string) {
  $string = str_replace('Indhold:', '', $string);
  $string = str_replace(' ; ', '<br/>', $string);
  $string = str_replace(' / ', '<br/>', $string);

  return $string;
}

/**
 * Implements hook_feed_icon().
 */
function alternator_feed_icon($url) {
  if ($image = theme('image', drupal_get_path('theme', 'dynamo') . '/images/feed.png', t('RSS feed'), t('RSS feed'))) {
    // Transform view expose query string in to drupal style arguments -- ?library=1 <-> /1
    if ($pos = strpos($url, '?')) {
      $base = substr($url, 0, $pos);
      $parm = '';
      foreach ($_GET as $key => $value) {
        if ($key != 'q') {
          $parm .= '/' . strtolower($value);
        }
      }

      // Extra fix for event arrangementer?library=x, as it wants taks. id/lib. id
      if (isset($_GET['library'])) {
        if (arg(1) == '') {
          $parm = '/all' . $parm;
        }
      }
      $url = $base . $parm;
    }
    return '<a href="' . check_url($url) . '" class="feed-icon">' .  $image . '<span>' . t('RSS') . '</span></a>';
  }
}

/**
 * Implements hook_theme().
 */
function alternator_theme() {
  return array(
    'user_login' => array(
      'arguments' => array('form' => NULL),
    ),
    'ting_search_form' => array(
      'arguments' => array('form' => NULL),
    ),
  );
}

/**
 * Theme function used to change the login box.
 */
function alternator_user_login($form) {
  unset($form['name']['#description']);
  unset($form['pass']['#description']);

  return drupal_render($form);
}

/**
 * Theme function that can be used to remove stuff form the search form. The
 * h2 headline can be disabled on the block for current theme.
 */
function alternator_ting_search_form(&$form) {
  unset($form['example_text']);
  return drupal_render($form);
}

/**
 * Theming the user loan list form.
 */
function alternator_ding_library_user_loan_list_form($form) {
  $date_format = variable_get('date_format_date_short', 'Y-m-d');
  $output = '';

  // Load ting client, its used get local object ids.
  module_load_include('client.inc', 'ting');

  // List each due data group in own list.
  foreach ($form['loan_data']['#grouped'] as $date => $group) {
    // Overdue loans get preferential treatment. No checkboxes here.
    if ($date == 'overdue') {
      $title = t('Overdue loans');
    }
    // The normal loans get grouped by due date.
    else {
      if ($date == 'due') {
        $title = t('Due today');
      }
      else {
        $title = t('Due in @count days, @date', array('@date' => date('d/m/y', strtotime($date)), '@count' => ceil((strtotime($date) - $_SERVER['REQUEST_TIME']) / 86400)));
      }
    }

    // Build list for each date group.
    $items = array();
    foreach ($group as $loan_id) {
      // Get information about the current loan.
      $loan = $form['loan_data']['#value'][$loan_id];
      $class = $loan['is_renewable'] ? 'selectable' : 'immutable';

      // Build information about the loan.
      $item = array(
        'checkbox' => drupal_render($form['loans'][$loan_id]),
        'title' => theme('ding_library_user_list_item', 'loan', $loan),
        'information' => array(
          'due_date'  => array(
            'label' => t('Due date'),
            'value' => ding_library_user_format_date($loan['due_date'], $date_format),
          ),
        ),
        'attributes' => array('class' => $class),
      );

      $items[] = $item;
    }

    // Render the items in this date group.
    if (!empty($items)) {
      $output .= theme('ding_mobile_reservation_item_list', $items, $title, array('class' => 'loan-list checkbox-list'));
    }
  }

  // Check if any loans where found.
  if (empty($output)) {
    return '<div class="no-loans">' . t('No loans found.') . '</div>';
  }
  else {
    // Add top buttons, wait until now, because there may not be any
    // reservations and the above statement will fail.
    if ($form['buttons']) {
      $form['top_buttons'] = $form['buttons'];
      // Add suffix to duplicated form button ids to ensure uniqueness.
      foreach (element_children($form['top_buttons']) as $key) {
        if (isset($form['top_buttons'][$key]['#id'])) {
          $form['top_buttons'][$key]['#id'] .= '-top';
        }
      }
      // Wrap top buttons in a wrapper div. This is a hack, sorry :-(
      $form['buttons']['renew']['#prefix'] = '<div class="button-element">';
      $form['buttons']['renew_all']['#suffix'] = '</div>';
      $form['top_buttons']['renew']['#prefix'] = '<div class="button-element">';
      $form['top_buttons']['renew_all']['#suffix'] = '</div>';

      $output = drupal_render($form['top_buttons']) . $output;
    }
  }

  $output .= drupal_render($form);
  return $output;
}

/**
 * Theming of reservation detailed list form.
 */
function alternator_ding_reservation_list_form($form) {
  $date_format = variable_get('date_format_date_short', 'Y-m-d');
  $output = '';

  // Load ting client, its used get local object ids.
  module_load_include('client.inc', 'ting');

  // If fetchable reservations is found.
  if (!empty($form['reservations']['#grouped']['fetchable'])) {

    $items = array();
    foreach ($form['reservations']['#grouped']['fetchable'] as $reservation) {
      $item = array(
        'checkbox' => drupal_render($form['selected'][$reservation['id']]),
        'title' => theme('ding_library_user_list_item', 'reservation', $reservation) . ' (<span class="reservation-number">' . t('Res. no @num', array('@num' => $reservation['id'])) . '</span>)',
        'information' => array(
          'queue_number'  => array('label' => t('Pickup number'), 'value' => $reservation['pickup_number']),
          'pickup_expire_date' => array('label' => t('Pickup by'), 'value' => ding_library_user_format_date($reservation['pickup_expire_date'], $date_format)),
          'pickup_branch' => array('label' => t('Pickup branch'), 'value' => $reservation['pickup_branch'] ? $reservation['pickup_branch'] : t('Unknown')),
        ),
      );

      $items[] = $item;
    }

    // Theme the items, the theme function is located in ding-mobile module.
    if (!empty($items)) {
      $output .= theme('ding_mobile_reservation_item_list', $items, t('Reservations ready for pickup'), array('class' => 'reservation-list checkbox-list'));
    }
  }

  // If avtive reservations is found.
  if (!empty($form['reservations']['#grouped']['active'])) {

    $items = array();
    foreach ($form['reservations']['#grouped']['active'] as $reservation) {
      $item = array(
        'checkbox' => drupal_render($form['selected'][$reservation['id']]),
        'title' => theme('ding_library_user_list_item', 'reservation', $reservation) . ' (<span class="reservation-number">' . t('Res. no @num', array('@num' => $reservation['id'])) . '</span>)',
        'information' => array(
          'queue_number'  => array('label' => t('Queue number'), 'value' => $reservation['queue_number']),
          'pickup_branch' => array('label' => t('Pickup branch'), 'value' => $reservation['pickup_branch'] ? $reservation['pickup_branch'] : t('Unknown')),
        ),
      );

      $items[] = $item;
    }

    // Theme the items, the theme function is located in ding-mobile module.
    if (!empty($items)) {
      $output .= theme('ding_mobile_reservation_item_list', $items, t('Active reservations'), array('class' => 'reservation-list checkbox-list'));
    }
  }

  // If output is empty, display text.
  if (empty($output)) {
    return '<div class="no-reservations">' . t('No reservations found.') . '</div>';
  }
  else {
    // Add top buttons, wait until now, because there may not be any
    // reservations and the above statement will fail.
    if ($form['buttons']) {
      $form['top_buttons'] = $form['buttons'];
      // Add suffix to duplicated form button ids to ensure uniqueness.
      foreach (element_children($form['top_buttons']) as $key) {
        if (isset($form['top_buttons'][$key]['#id'])) {
          $form['top_buttons'][$key]['#id'] .= '-top';
        }
      }
      // Wrap top buttons in a wrapper div. This is a hack, sorry :-(
      $form['buttons']['update']['#prefix'] = '<div class="button-element">';
      $form['buttons']['remove']['#suffix'] = '</div>';
      $form['top_buttons']['update']['#prefix'] = '<div class="button-element">';
      $form['top_buttons']['remove']['#suffix'] = '</div>';

      // Render top buttons and put theme in front of the output.
      $output = drupal_render($form['top_buttons']) . $output;
    }
  }

  // Render options e.i. pickup branch and validation periode.
  $output .= '<div class="update-controls clear-block">';
  $output .= drupal_render($form['options']);
  $output .= '</div>';

  // Render bottom buttons.
  $output .= '<div class="update-controls-button clear-block">';
  $output .= drupal_render($form['buttons']);
  $output .= '</div>';

  $output .= drupal_render($form);

  return $output;
}

/**
 * Theming of debt details form.
 */
function alternator_ding_debt_list_form($form) {
  $date_format = variable_get('date_format_date_short', 'Y-m-d');
  $items = array();

  // Loop through the payments and create array based on it.
  foreach ($form['debts']['#value'] as $key => $data) {
    $item = array(
      'checkbox' => drupal_render($form['selected'][$data['id']]),
      'title' => $data['display_title'],
      'information' => array(
        'data'  => array('label' => t('Date'), 'value' => ding_library_user_format_date($data['date'], $date_format)),
        'amount' => array('label' => t('Amount'), 'value' => $data['amount']),
      ),
    );

    $items[] = $item;
  }

  // Render a mobile friendly list.
  $output .= theme('ding_mobile_reservation_item_list', $items, t('Avaliable payments'), array('class' => 'reservation-list checkbox-list'));
  $output .= drupal_render($form);

  return $output;
}
