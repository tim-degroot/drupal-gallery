<?php

namespace Drupal\s3_gallery\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Aws\S3\S3Client;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Site\Settings; // Add this line

/**
 * Provides route responses for the S3 Gallery module.
 */
class GalleryController extends ControllerBase {

  protected $configFactory;
  protected $s3Client;

  public function __construct(ConfigFactoryInterface $config_factory, S3Client $s3_client) {
    $this->configFactory = $config_factory;
    $this->s3Client = $s3_client;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('aws.s3_client')
    );
  }

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

      // Set the folder to be listed manually
      $folder = 'photos/20241106 Boulderen/'; // Change this to the desired folder

      // List objects in the selected folder
      $objects = $s3->listObjectsV2([
        'Bucket' => $bucket,
        'Prefix' => $folder,
      ]);

      $output = '';
      if (isset($objects['Contents'])) {
        $output .= "<h1>Images in '{$folder}'</h1>";
        $output .= "<div class='gallery'>";

        foreach ($objects['Contents'] as $object) {
          $url = $s3->getObjectUrl($bucket, $object['Key']);
          $output .= "<img src='{$url}' alt='Image'>";
        }

        $output .= "</div>";
      } else {
        $output .= "No objects found in '{$folder}'.";
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