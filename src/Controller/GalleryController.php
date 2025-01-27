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
   * Returns the title for the gallery pages.
   *
   * @param string|null $prefix
   *   The prefix for the S3 objects.
   *
   * @return string
   *   The title for the gallery page.
   */
  public function getTitle($prefix = '') {
    if (empty($prefix)) {
      return 'Photo Gallery';
    }
    return 'Photo Gallery: ' . urldecode($prefix);
  }

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

      // List objects in the specified prefix
      $contents = $s3->listObjectsV2([
        'Bucket' => $bucket,
        'Prefix' => $prefix,
        'Delimiter' => '/', // Ensure only direct children are listed
      ]);

      // $output .= "<h3>Contents raw:</h3>";
      // $output .= $contents;
      // $output .= "<h3>The contents of your bucket are:</h3>";
      // $output .= "<ul>";
      // if (isset($contents['Contents'])) {
      //   foreach ($contents['Contents'] as $content) {
      //     $output .= "<li>" . htmlspecialchars($content['Key']) . "</li>";
      //   }
      // }
      // $output .= "</ul>";

      $output .= "<h3>The CommonPrefixes are:</h3>";
      $output .= "<ul>";
      if (isset($contents['CommonPrefixes'])) {
        foreach ($contents['CommonPrefixes'] as $commonPrefix) {
          $prefix = htmlspecialchars($commonPrefix['Prefix']);
          $splitPrefix = explode('/', trim($prefix, '/'));
          array_shift($splitPrefix); // remove the first entry
          $url = "/photos/" . implode('/', $splitPrefix);
          $output .= "<li><a href=\"$url\">" . implode(' > ', $splitPrefix) . "</a></li>";
        }
      }
      $output .= "</ul>";

      $prefixes_by_year = [];

      if (isset($contents['CommonPrefixes'])) {
          foreach ($contents['CommonPrefixes'] as $commonPrefix) {
              $prefix = htmlspecialchars($commonPrefix['Prefix']);
              $year = substr($prefix, 7, 4); // Extract the year (first 4 symbols)
              if (!isset($prefixes_by_year[$year])) {
                  $prefixes_by_year[$year] = [];
              }
              $prefixes_by_year[$year][] = $prefix;
          }
      }

      // Sort the prefixes by year
      krsort($prefixes_by_year);

      foreach ($prefixes_by_year as $year => $prefixes) {
        // Sort the prefixes alphabetically by their first part after removing the first entry
        usort($prefixes, function($a, $b) {
            $a_split = explode('/', trim($a, '/'));
            $b_split = explode('/', trim($b, '/'));
            array_shift($a_split); // remove the first entry
            array_shift($b_split); // remove the first entry
            return strcmp(implode('/', $a_split), implode('/', $b_split));
        });
    
        $output .= "<h3>$year</h3>";
        $output .= "<ul>";
        foreach ($prefixes as $prefix) {
            $splitPrefix = explode('/', trim($prefix, '/'));
            array_shift($splitPrefix); // remove the first entry
            $url = "/photos/" . implode('/', $splitPrefix);
            $output .= "<li><a href=\"$url\">" . implode(' > ', $splitPrefix) . "</a></li>";
        }
        $output .= "</ul>";
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
      $expires = '+10 minutes';

      // Print the current prefix
      $output = "";
      // $output .= "<h3>Current prefix: " . htmlspecialchars($prefix) . "</h3>";

      // List objects in the specified prefix
      $contents = $s3->listObjectsV2([
        'Bucket' => $bucket,
        'Prefix' => $prefix,
      ]);

      $albums = $s3->listObjectsV2([
        'Bucket' => $bucket,
        'Prefix' => $prefix,
        'Delimiter' => '/',
      ]);

      $output .= "<h3>The CommonPrefixes are:</h3>";
      $output .= "<ul>";
      if (isset($albums['CommonPrefixes'])) {
        foreach ($albums['CommonPrefixes'] as $commonPrefix) {
          $prefix = htmlspecialchars($commonPrefix['Prefix']);
          $splitPrefix = explode('/', trim($prefix, '/'));
          array_shift($splitPrefix); // remove the first entry
          $url = "/photos/" . implode('/', $splitPrefix);
          $output .= "<li><a href=\"$url\">" . implode(' > ', $splitPrefix) . "</a></li>";
        }
      }
      $output .= "</ul>";
      $output .= "<h3>The contents of your bucket are:</h3>:";
      $output .= "<div class=\"grid-wrapper\">";
      
      if (isset($contents['Contents'])) {
        foreach ($contents['Contents'] as $content) {
          $key = htmlspecialchars($content['Key']);
          $url = $s3->getObjectUrl($bucket, $key);
          // $output .= "<li><img src=\"$url\" alt=\"$key\" style=\"max-width: 200px;\" /></li>";
          $output .= "<div style=\" max-width: 400px; \"><img src=\"$url\"/></div>";
              }
            }
      $output .= "</div>";
      
      return [
        '#markup' => $output,
        'css' => [
          'theme' => [
              'css/custom.css' => [],
          ],
        ],
        
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