<?php

namespace Drupal\s3_gallery\Controller;

use Drupal\Core\Controller\ControllerBase;
use Aws\S3\S3Client;
use Drupal\Core\Site\Settings;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides route responses for the S3 Gallery module.
 */
class TentamenbankController extends ControllerBase {

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
      return 'Tentamenbank';
    }
    // $date = date_create(substr($prefix, 0, 8));
    // $title = substr($prefix, 8);
    // // $month = substr($displayText, 0, 2);
    // // $day = substr($displayText, 2, 2);
    // // $placeholder = substr($displayText, 4); // Extract the rest of the string
    
    // // Reformat to DD/MM {Placeholder}
    // $displayText = date_format($date, "D j M Y") . " —" . $title;
    return $prefix;
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
      $prefix = 'tentamenbank/';

      $contents = $s3->listObjectsV2([
        'Bucket' => $bucket,
        'Prefix' => $prefix,
      ]);



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
      $prefix = 'tentamenbank/' . urldecode($prefix); // Ensure 'photos/' is prefixed and decode the prefix
      $contents = $s3->listObjectsV2([
        'Bucket' => $bucket,
        'Prefix' => $prefix,
      ]);
      
      if ($prefix == 'tentamenbank/') {
        $output = $this->homePage($content);
      } else {
        $output = $this->tentamensPage($content);
      }
      
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

  private function homePage($contents) { 
    $output = "";

      $output .= "<h3>Contents raw:</h3>";
      $output .= $contents;
      $output .= "<h3>The contents of your bucket are:</h3>";
      $output .= "<ul>";
      if (isset($contents['Contents'])) {
        $uniqueKeys = [];
        foreach ($contents['Contents'] as $content) {
            $key = htmlspecialchars($content['Key']);
            $strippedKey = substr($key, 0, strrpos($key, '/'));
    
            if (!in_array($strippedKey, $uniqueKeys)) {
                $uniqueKeys[] = $strippedKey;
    
                $splitKey = explode('/', trim($strippedKey, '/'));
                if (count($splitKey) >= 3) {
                    $study = $splitKey[1];
                    $subject = $splitKey[2];
                    $url = "/tentamenbank/" . $study . "/" . $subject;
                    $output .= "<li>Study: $study, Subject: $subject, URL: <a href=\"$url\">$url</a></li>";
                }
            }
        }
    }
      $output .= "</ul>";
  }

  private function tentamensPage($contents) {
     
  }
}