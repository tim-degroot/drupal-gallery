<?php

namespace Drupal\s3_gallery\Controller;

use Drupal\Core\Controller\ControllerBase;
use Aws\S3\S3Client;
use Drupal\Core\Site\Settings;
use Symfony\Component\HttpFoundation\Request;

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
      $prefix = 'photos/' . urldecode($prefix); // Ensure 'photos/' is prefixed and decode the prefix

      // Print the current prefix
      $output = "Current prefix: " . htmlspecialchars($prefix) . "<br>";

      // List objects in the specified prefix
      $contents = $s3->listObjectsV2([
        'Bucket' => $bucket,
        'Prefix' => $prefix,
      ]);

      echo $contents

      $output .= "The contents of your bucket are: <br>";
      foreach ($contents['Contents'] as $content) {
        $output .= $content['Key'] . "<br>";
      }

      // Return the output as a renderable array
      return [
        '#markup' => $output,
      ];
    } catch (\Exception $e) {
      // Debugging information
      \Drupal::logger('s3_gallery')->error('Error: @error', ['@error' => $e->getMessage()]);
      return [
        '#markup' => "Error: " . $e->getMessage(),
      ];
    }
  }
}