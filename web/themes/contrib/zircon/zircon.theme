<?php

use Drupal\Component\Utility\Xss;
use Drupal\Core\Template\Attribute;

function zircon_preprocess_html(&$variables) {
  // Add information about the number of sidebars.
}

function zircon_preprocess_page(&$variables) {
  if (!empty($variables['page']['sidebar_first']) && !empty($variables['page']['sidebar_second'])) {
    $variables['attributes']['class'][] = 'layout-two-sidebars';
    $variables['page']['main_content_width'] = 6;    
  }
  elseif (!empty($variables['page']['sidebar_first'])) {
    $variables['attributes']['class'][] = 'layout-one-sidebar';
    $variables['attributes']['class'][] = 'layout-sidebar-first';
    $variables['page']['main_content_width'] = 9;    
  }
  elseif (!empty($variables['page']['sidebar_second'])) {
    $variables['attributes']['class'][] = 'layout-one-sidebar';
    $variables['attributes']['class'][] = 'layout-sidebar-second';
    $variables['page']['main_content_width'] = 9;    
  }
  else {
    $variables['attributes']['class'][] = 'layout-no-sidebars';
    $variables['page']['main_content_width'] = 12;    
  }
  
  //$variables['logo'] = str_replace('.svg', '.png', $variables['logo']);
  //print_r($variables);
  //exit;
}