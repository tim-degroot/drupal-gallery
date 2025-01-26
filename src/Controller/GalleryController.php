<?php
namespace Drupal\s3_gallery\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Provides route responses for the Example module.
 */
class GalleryController extends ControllerBase {

  /**
   * Returns a simple page.
   *
   * @return array
   *   A simple renderable array.
   */
  public function myPage() {
    return [
    '#markup' => $this->t('This is the gallery page.'),    ];
  }

}