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
  public function getTitle($study = '', $subject = '') {
    return $subject;
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
      $prefix = 'tentamenbank/'; // Ensure 'photos/' is prefixed and decode the prefix
      $contents = $s3->listObjectsV2([
        'Bucket' => $bucket,
        'Prefix' => $prefix,
      ]);

      $output = $this->homePage($contents);



      return [
        '#theme' => 'tentamenbank_subject',
        '#subjects' => $result,
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
  public function myPage($study = '', $subject = '') {
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
      $prefix = 'tentamenbank/' . urldecode($study) . '/' . urldecode($subject); // Ensure 'photos/' is prefixed and decode the prefix
      $contents = $s3->listObjectsV2([
        'Bucket' => $bucket,
        'Prefix' => $prefix,
      ]);

      $output = $this->tentamensPage($contents);
      
      
      return [
        $output;
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
    $result = [];
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
                    $result[] = [
                        'study' => $study,
                        'subject' => $subject,
                        'url' => $url,
                    ];
                }
            }
        }
    }

      return $result;
  }

  private function tentamensPage($contents) {
    $output = '';

    if (isset($contents['Contents'])) {
      foreach ($contents['Contents'] as $content) {
          $key = htmlspecialchars($content['Key']);
          $splitKey = explode('/', trim($key, '/'));
          $lastElement = end($splitKey);
          $output .= $lastElement . '<br>';
      }
    


  }

  return $output;
}
}