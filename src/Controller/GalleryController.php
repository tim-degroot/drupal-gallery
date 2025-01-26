<?php

namespace Drupal\s3_gallery\Controller;

use Drupal\Core\Controller\ControllerBase;
use Aws\S3\S3Client;
use Drupal\Core\Site\Settings;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Url;

/**
 * Provides route responses for the S3 Gallery module.
 */
class GalleryController extends ControllerBase {

  /**
   * Returns a gallery page.
   *
   * @param string|null $prefix
   *   The prefix for the S3 objects.
   *
   * @return array
   *   A renderable array.
   */
  public function myPage($prefix = '') {
    try {
      // Retrieve AWS S3 configuration from settings.php
      $config = Settings::get('aws_s3');
      $s3 = new S3Client([
        'version' => 'latest',
        'region' => $config['region'],
        'credentials' => [
          'key'    => $config['key'],
          'secret' => $config['secret'],
        ],
      ]);

      $bucket = 'acdweb-storage';
      $prefix = 'photos/' . $prefix; // Ensure 'photos/' is prefixed

      // List objects in the specified prefix
      $objects = $s3->listObjectsV2([
        'Bucket' => $bucket,
        'Prefix' => $prefix,
        'Delimiter' => '/',
      ]);

      $output = '';
      if (isset($objects['Contents'])) {
        $output .= "<div class='gallery-urls'>";
        foreach ($objects['Contents'] as $object) {
          $key = $object['Key'];
          if (substr($key, -1) !== '/') { // Check if it's not a folder
            $url = $s3->getObjectUrl($bucket, $key);
            $output .= "<div class='gallery-url'>";
            $output .= "<a href='{$url}'>{$key}</a>";
            $output .= "</div>";
          }
        }
        $output .= "</div>";
      } else {
        $output .= "No images found in '{$prefix}'.";
      }

      return [
        '#markup' => $output,
      ];
    } catch (\Exception $e) {
      return [
        '#markup' => "Error: " . $e->getMessage(),
      ];
    }
  }
}