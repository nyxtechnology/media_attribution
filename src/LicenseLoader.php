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
  public static function createOrUpdateLicenseTerm($term_title, $term_short_label, $icon_file_path, $license_url) {
    $tids = array_values(\Drupal::entityQuery('taxonomy_term')
      ->condition('name', $term_title)
      ->execute());

    if ($tids) {
      self::updateLicenseTerm($tids[0], $term_title, $term_short_label, $icon_file_path, $license_url);
      return $tids[0];
    }
    else {
      return self::createLicenseTerm($term_title, $term_short_label, $icon_file_path, $license_url);
    }
  }

  /**
   * Craete a new license taxonomy term entity.
   *
   * @param $term_title
   * @param $term_short_label
   * @param $icon_file_path
   * @param $license_url
   * @return int
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public static function createLicenseTerm($term_title, $term_short_label, $icon_file_path, $license_url) {
    if ($icon_file_path) {
      $icon_file = self::updateLicenseIcon($icon_file_path);
      $tid = Term::create([
        'name' => $term_title,
        'vid' => 'media_attribution_licenses',
        'field_license_link' => ['title' => $term_short_label, 'uri' => $license_url],
        'field_license_icon' => [
          'target_id' => $icon_file->id(),
          'alt' => $term_title,
          'title' => $term_title,
        ]
      ])->save();
    } else {
      // Create the term without an image.
      $tid = Term::create([
        'name' => $term_title,
        'vid' => 'media_attribution_licenses',
        'field_license_link' => ['title' => $term_short_label, 'uri' => $license_url],
      ])->save();
    }

    return $tid;
  }

  /**
   * Update an existing license term.
   *
   * @param $tid
   * @param $term_title
   * @param $term_short_label
   * @param $icon_file_path
   * @param $license_url
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public static function updateLicenseTerm($tid, $term_title, $term_short_label, $icon_file_path, $license_url) {
    $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($tid);
    $term->set('name', $term_title);
    $term->set('field_license_link', ['title' => $term_short_label, 'uri' => $license_url]);
    if ($icon_file_path) {
      $icon_file = self::updateLicenseIcon($icon_file_path);

      $term->set('field_license_icon', [
        'target_id' => $icon_file->id(),
        'alt' => $term_title,
        'title' => $term_title,
      ]);
    }
    $term->save();
  }

  /**
   * Create or update a license icon file.
   *
   * @param $icon_file_path
   * @return array
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public static function updateLicenseIcon($icon_file_path) {
// Just in case the file has already been created.
    $icon_files = \Drupal::entityTypeManager()
      ->getStorage('file')
      ->loadByProperties(['uri' => $icon_file_path]);
    $icon_file = reset($icon_files);
    // if not create a file

    if (!$icon_file) {
      if (substr($icon_file_path, 0, strlen(DRUPAL_ROOT)) == DRUPAL_ROOT) {
        $icon_uri = substr($icon_file_path, strlen(DRUPAL_ROOT), strlen($icon_file_path));
      }
      elseif (substr($icon_file_path, 0, 1) != '/') {
        $icon_uri = $icon_file_path;
      }
      else {
        $fs = \Drupal::service('file_system');
        $icon_uri = $fs->copy($icon_file_path, 'public://');
      }
      $icon_file = File::create([
        'uri' => $icon_uri,
      ]);
      $icon_file->save();
    }
    return $icon_file;
  }
}