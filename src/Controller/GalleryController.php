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
   * Returns the main gallery page.
   *
   * @return array
   *   A renderable array.
   */
  public function mainPage() {
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

      // Print the current prefix
      $output = "<h2>Main Photo Gallery</h2>";
      $output .= "<h3>Current prefix: " . htmlspecialchars($prefix) . "</h3>";

      // List objects in the specified prefix
      $contents = $s3->listObjectsV2([
        'Bucket' => $bucket,
        'Prefix' => $prefix,
        'Delimiter' => '/', // Ensure only direct children are listed
      ]);

      $output .= "<h3>The contents of your bucket are:</h3>";
      $output .= "<ul>";
      if (isset($contents['Contents'])) {
        foreach ($contents['Contents'] as $content) {
          $output .= "<li>" . htmlspecialchars($content['Key']) . "</li>";
        }
      }
      $output .= "</ul>";

      $output .= "<h3>The CommonPrefixes are:</h3>";
      $output .= "<ul>";
      if (isset($contents['CommonPrefixes'])) {
        foreach ($contents['CommonPrefixes'] as $commonPrefix) {
          $output .= "<li>" . htmlspecialchars($commonPrefix['Prefix']) . "</li>";
        }
      }
      $output .= "</ul>";

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
      $output = "<h2>Photo Gallery</h2>";
      $output .= "<h3>Current prefix: " . htmlspecialchars($prefix) . "</h3>";

      // List objects in the specified prefix
      $contents = $s3->listObjectsV2([
        'Bucket' => $bucket,
        'Prefix' => $prefix,
      ]);

      $output .= "<h3>The contents of your bucket are:</h3>";
      $output .= "<ul>";
      if (isset($contents['Contents'])) {
        foreach ($contents['Contents'] as $content) {
          $output .= "<li>" . htmlspecialchars($content['Key']) . "</li>";
        }
      }
      $output .= "</ul>";

      $output .= "<h3>The CommonPrefixes are:</h3>";
      $output .= "<ul>";
      if (isset($contents['CommonPrefixes'])) {
        foreach ($contents['CommonPrefixes'] as $commonPrefix) {
          $output .= "<li>" . htmlspecialchars($commonPrefix['Prefix']) . "</li>";
        }
      }
      $output .= "</ul>";

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