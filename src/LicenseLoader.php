<?php

namespace Drupal\media_attribution;

use Drupal\Core\File\FileSystemInterface;
use Drupal\taxonomy\Entity\Term;
use Drupal\file\Entity\File;
use phpDocumentor\Reflection\Types\Integer;

class LicenseLoader {

  /**
   * Create a new license term with the given values.
   *
   * @param $term_title
   *   Long-form title.
   * @param $term_short_label
   *   Short-form title
   * @param $icon_file_path
   *   Icon file path.
   * @param $license_url
   *   URL for the license home page.
   * @return int
   *   The new term id.
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public static function createLicenseTerm($term_title, $term_short_label, $icon_file_path, $license_url) {
    if ($icon_file_path) {
      // Just in case the file has already been created.
      $icon_files = \Drupal::entityTypeManager()
        ->getStorage('file')
        ->loadByProperties(['uri' => $icon_file_path]);
      $icon_file = reset($icon_files);
      // if not create a file

      if (!$icon_file) {
        $fs = \Drupal::service('file_system');
        $icon_uri = $fs->copy($icon_file_path, 'public://');

        $icon_file = File::create([
          'uri' => $icon_uri,
        ]);
        $icon_file->save();
      }
      $tid = Term::create([
        'name' =>  $term_title,
        'vid' => 'media_attribution_licenses',
        'field_license_link' =>  ['title' => $term_short_label, 'uri' => $license_url],
        'field_license_icon' => [
          'target_id' => $icon_file->id(),
          'alt' => $term_title,
          'title' => $term_title,
        ]
      ])->save();
    }
    else {
      // Create the term without an image.
      $tid = Term::create([
        'name' =>  $term_title,
        'vid' => 'media_attribution_licenses',
        'field_license_link' =>  ['title' => $term_short_label, 'uri' => $license_url],
      ])->save();
    }

    return $tid;
  }
}