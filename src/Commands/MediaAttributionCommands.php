<?php

namespace Drupal\media_attribution\Commands;

use Drush\Commands\DrushCommands;
use Drupal\Component\Serialization\Yaml;
use Drupal\media_attribution\LicenseLoader;

class MediaAttributionCommands extends DrushCommands {

  /**
   * Loads licenses from a YAML file. The format is:
   *
   *   -
   *     title:
   *     short_label:
   *     url:
   *     icon_file:
   *
   * @param $file
   *   YAML file containing licenses to load.
   *
   * @command media_attribution:load_licenses
   * @aliases ma-load
   *
   * @usage media_attribution:load_licenses licenses_file.yml
   *   Load the contents of the licenses file as new terms in the Media Attribution Licenses vocabulary.
   */
  public function loadLicenses($file) {

    $config = $this->getConfig();
    $config->get('cwd');
    $this->output()->writeln("Loading licenses from $file.");
    $cwd = $this->config->get('env')['cwd'];
    $full_path = $cwd . '/' . $file;
    $file_contents = file_get_contents($full_path);
    $license_data = Yaml::decode($file_contents);

    foreach ($license_data as $license_item) {
      $icon_file_path = '';
      if (!empty($license_item['icon_file'])) {
        $icon_file_path = substr($license_item['icon_file'], 0, 1) == '/' ? $license_item['icon_file'] : $cwd . '/' . $license_item['icon_file'];
      }
      LicenseLoader::createOrUpdateLicenseTerm($license_item['title'],$license_item['short_label'], $icon_file_path, $license_item['url']);
    }

  }

}