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
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request object.
   *
   * @return array
   *   A renderable array.
   */
  public function myPage(Request $request) {
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
      $prefix = $request->query->get('prefix', 'photos/');

      // List objects in the specified prefix
      $objects = $s3->listObjectsV2([
        'Bucket' => $bucket,
        'Prefix' => $prefix,
        'Delimiter' => '/',
      ]);

      $output = '';
      if (isset($objects['CommonPrefixes']) || isset($objects['Contents'])) {
        $output .= "<h1>Gallery</h1>";
        $output .= "<div class='gallery'>";

        if (isset($objects['CommonPrefixes'])) {
          foreach ($objects['CommonPrefixes'] as $commonPrefix) {
            $folderName = rtrim($commonPrefix['Prefix'], '/');
            $folderUrl = \Drupal::url('s3_gallery.my_page', [], ['query' => ['prefix' => $folderName . '/']]);
            $output .= "<div class='gallery-item'>";
            $output .= "<a href='{$folderUrl}'>{$folderName}</a>";
            $output .= "</div>";
          }
        }

        if (isset($objects['Contents'])) {
          foreach ($objects['Contents'] as $object) {
            $key = $object['Key'];
            if (substr($key, -1) !== '/') { // Check if it's not a folder
              $result = $s3->getObject([
                'Bucket' => $bucket,
                'Key'    => $key,
              ]);
              $imageData = base64_encode($result['Body']);
              $output .= "<div class='gallery-item'>";
              $output .= "<img src='data:image/jpeg;base64,{$imageData}' alt='{$key}' />";
              $output .= "</div>";
            }
          }
        }

        $output .= "</div>";
      } else {
        $output .= "No images or folders found in '{$prefix}'.";
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