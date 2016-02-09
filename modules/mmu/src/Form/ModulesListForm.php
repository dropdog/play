<?php

/**
 * @file
 * Contains \Drupal\mmu\Form\ModulesListForm.
 */

namespace Drupal\mmu\Form;

use Drupal\Component\Utility\SafeMarkup;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\Component\Utility\Html;
use Drupal\system\Form\ModulesListForm as SystemModuleListForm;

/**
 * {@inheritdoc}
 */
class ModulesListForm extends SystemModuleListForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    require_once DRUPAL_ROOT . '/core/includes/install.inc';
    $distribution = SafeMarkup::checkPlain(drupal_install_profile_distribution_name());

    // Include system.admin.inc so we can use the sort callbacks.
    $this->moduleHandler->loadInclude('system', 'inc', 'system.admin');

    // Sort all modules by their names.
    $modules = system_rebuild_module_data();
    uasort($modules, 'system_sort_modules_by_info_name');

    $packages = ['any' => t('- ANY -')];

    // Iterate over each of the modules.
    $form['modules']['#tree'] = TRUE;
    foreach ($modules as $machine_name => $module) {
      if (empty($module->info['hidden'])) {
        $form['modules'][$machine_name] = $this->buildRow($modules, $module, $distribution);
        $form['modules'][$machine_name]['#theme'] = 'mmu_modules_item';
        $form['modules'][$machine_name]['#module'] = $module;
        $form['modules'][$machine_name]['#machine_name'] = $machine_name;

        $package = $module->info['package'];
        $packages[HTML::getClass($package)] = $package;
      }
    }
    ksort($packages);

    $defaults = [];
    if (isset($_COOKIE['mmu-active-filter'])) {
      $filter_defaults = explode('.mmu-', SafeMarkup::checkPlain($_COOKIE['mmu-active-filter']));
      array_shift($filter_defaults);
      foreach ($filter_defaults as $filter_default) {
        list($key, $value) = array_pad(explode('-', $filter_default, 2), 2, NULL);
        $defaults[$key] = $value;
      }
    }

    if (isset($_COOKIE['mmu-active-sort'])) {
      list($defaults['sort_by'], $defaults['sort_order'])
        = array_pad(explode(':', SafeMarkup::checkPlain($_COOKIE['mmu-active-sort'])), 2, NULL);
    }

    $form['controls'] = array(
      '#title' => 'controls',
      '#type' => 'container',
      '#attributes' => ['class' => ['mmu-controls', 'clearfix']],
      '#weight' => -100,
    );

    $form['controls']['text'] = [
      '#type' => 'search',
      '#title' => t('Search'),
      '#size' => 30,
      '#placeholder' => $this->t('Enter module name'),
      '#attributes' => array(
        'class' => array('table-filter-text'),
        'data-table' => '#system-modules',
        'autocomplete' => 'off',
      ),
    ];

    $form['controls']['package'] = [
      '#title' => t('Package'),
      '#type' => 'select',
      '#options' => $packages,
      '#default_value' => isset($defaults['package']) ? $defaults['package'] : '',
    ];

    $form['controls']['status'] = [
      '#title' => t('Status'),
      '#type' => 'select',
      '#options' => [
        'any' => t('- ANY -'),
        'selected' => t('Selected'),
        'enabled' => t('Enabled'),
        'disabled' => t('Disabled'),
      ],
      '#default_value' => isset($defaults['status']) ? $defaults['status'] : '',
    ];

    $form['controls']['source'] = [
      '#title' => t('Source'),
      '#type' => 'select',
      '#options' => [
        'any' => t('- ANY -'),
        'core' => t('Core'),
        'contrib' => t('Contrib'),
        'custom' => t('Custom'),
      ],
      '#default_value' => isset($defaults['source']) ? $defaults['source'] : '',
    ];

    $form['controls']['sort_by'] = [
      '#title' => t('Sort by'),
      '#type' => 'select',
      '#options' => [
        'package' => t('Package'),
        'name' => t('Name'),
        'status' => t('Status'),
      ],
      '#default_value' => isset($defaults['sort_by']) ? $defaults['sort_by'] : '',
    ];

    $form['controls']['sort_order'] = [
      '#title' => t('Order'),
      '#type' => 'select',
      '#options' => [
        'asc' => t('Asc'),
        'desc' => t('Desc'),
      ],
      '#default_value' => isset($defaults['sort_order']) ? $defaults['sort_order'] : '',
    ];

    $form['controls']['reset'] = [
      '#title' => t('Order'),
      '#type' => 'button',
      '#value' => t('Reset'),
      '#attributes' => ['class' => ['mmu-reset-button']],
      '#prefix' => '<div class="form-item"><div>&nbsp;</div>',
      '#suffix' => '</div>',
    ];

    $form['modules']['#prefix'] = '<div id="mmu-container" class="clearfix">';
    $form['modules']['#suffix'] = '</div>';

    $form['controls']['actions'] = array(
      '#type' => 'actions',
      '#attributes' => ['class' => ['form-item']],
    );
    $form['controls']['actions']['total_selected'] = array(
      '#type' => 'container',
      '#markup' => '&nbsp;',
    );

    $form['controls']['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Save configuration'),
    );

    $form['summary'] = array(
      '#type' => 'item',
      '#markup' => '',
    );

    $form['#attached']['library'][] = 'mmu/mmu';
    $form['#attached']['library'][] = 'core/jquery.cookie';
    $form['#attached']['library'][] = 'core/drupal.debounce';
    $form['#attached']['library'][] = 'core/jquery.ui.dialog';
    $form['#attached']['library'][] = 'core/jquery.ui.effects.explode';

    $form['#attributes']['class'][] = 'mmu-modules-list';
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function buildModuleList(FormStateInterface $form_state) {
    $submitted_modules = $form_state->getValue('modules');

    // Build a list of modules to install.
    $modules = array(
      'install' => array(),
      'dependencies' => array(),
    );

    // Required modules have to be installed.
    // @todo This should really not be handled here.
    $data = system_rebuild_module_data();
    foreach ($data as $name => $module) {
      if (!empty($module->required) && !$this->moduleHandler->moduleExists($name)) {
        $modules['install'][$name] = $module->info['name'];
      }
    }

    // First, build a list of all modules that were selected.
    foreach ($submitted_modules as $name => $checkbox) {
      if ($checkbox['enable'] && !$this->moduleHandler->moduleExists($name)) {
        $modules['install'][$name] = $data[$name]->info['name'];
      }
    }

    // Add all dependencies to a list.
    while (list($module) = each($modules['install'])) {
      foreach (array_keys($data[$module]->requires) as $dependency) {
        if (!isset($modules['install'][$dependency]) && !$this->moduleHandler->moduleExists($dependency)) {
          $modules['dependencies'][$module][$dependency] = $data[$dependency]->info['name'];
          $modules['install'][$dependency] = $data[$dependency]->info['name'];
        }
      }
    }

    // Make sure the install API is available.
    include_once DRUPAL_ROOT . '/core/includes/install.inc';

    // Invoke hook_requirements('install'). If failures are detected, make
    // sure the dependent modules aren't installed either.
    foreach (array_keys($modules['install']) as $module) {
      if (!drupal_check_module($module)) {
        unset($modules['install'][$module]);
        foreach (array_keys($data[$module]->required_by) as $dependent) {
          unset($modules['install'][$dependent]);
          unset($modules['dependencies'][$dependent]);
        }
      }
    }

    return $modules;
  }

}
