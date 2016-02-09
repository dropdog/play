<?php

/**
 * @file
 * Drush integration for Mix Modules Up module.
 */

/**
 * The MixItUp library URLs.
 */

const MMU_DOWNLOAD_JS_URL = 'https://raw.githubusercontent.com/patrickkunka/mixitup/master/src/jquery.mixitup.js';

/**
 * Implements hook_drush_command().
 */
function mmu_drush_command() {
  $items['mmu-download'] = [
    'description' => dt('Download and install MixItUp library.'),
    'bootstrap' => DRUSH_BOOTSTRAP_DRUSH,
    'aliases' => ['mmu-d'],
  ];
  return $items;
}

/**
 * Command to download the MixItUp library.
 */
function drush_mmu_download() {

  $libraries_dir = 'libraries';

  if (!is_dir($libraries_dir)) {
    return drush_set_error('MMU', dt('Directory !libraries_dir does not exist.', ['!libraries_dir' => $libraries_dir]));
  }

  if (is_dir(MMU_LIBRARY_DIR)) {
    if (drush_confirm(dt('Install location !dir already exists. Do you want to overwrite it?', ['!dir' => MMU_LIBRARY_DIR]))) {
      drush_delete_dir(MMU_LIBRARY_DIR, TRUE);
    }
    else {
      return drush_log(dt('Aborting.'));
    }
  }

  drush_mkdir(MMU_LIBRARY_DIR);

  drush_shell_exec('wget --timeout=15 -O %s %s', MMU_LIBRARY_DIR . '/jquery.mixitup.min.js', MMU_DOWNLOAD_JS_URL);

  $js_file_found = drush_file_not_empty(MMU_LIBRARY_DIR . '/jquery.mixitup.min.js');
  if (!$js_file_found) {
    return drush_set_error('MMU', dt('Could not download MixItUp library from GitHub.'));
  }
  else {
    drush_log(dt('MixItUp library has been installed in @path', ['@path' => MMU_LIBRARY_DIR]), 'success');
  }

}
