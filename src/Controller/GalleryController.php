<?php

namespace Drupal\s3_gallery\Controller;

use Drupal\Core\Controller\ControllerBase;
use Aws\S3\S3Client;
use Drupal\Core\Site\Settings;

/**
 * Provides route responses for the S3 Gallery module.
 */
class GalleryController extends ControllerBase {

  /**
   * Returns a gallery page.
   *
   * @return array
   *   A renderable array.
   */
  public function myPage() {
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
      $prefix = 'photos/';

      // List objects in the specified prefix
      $objects = $s3->listObjectsV2([
        'Bucket' => $bucket,
        'Prefix' => $prefix,
        'Delimiter' => '/',
      ]);

      $output = '';
      if (isset($objects['CommonPrefixes'])) {
        $output .= "<h1>Folders in '{$prefix}'</h1>";
        $output .= "<ul>";

        foreach ($objects['CommonPrefixes'] as $commonPrefix) {
          $folderName = rtrim($commonPrefix['Prefix'], '/');
          $output .= "<li>{$folderName}</li>";
        }

        $output .= "</ul>";
      } else {
        $output .= "No folders found in '{$prefix}'.";
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