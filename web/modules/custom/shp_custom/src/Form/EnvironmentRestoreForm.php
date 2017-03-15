<?php

namespace Drupal\shp_custom\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;

/**
 * Class SiteCloneForm.
 *
 * @package Drupal\shp_custom\Form
 */
class EnvironmentRestoreForm extends FormBase {

  const MACHINE_NAMES = [
    'dev' => 'DEV',
    'uat' => 'UAT',
    'prd' => 'PRD',
  ];

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'shp_custom_environment_restore_form';
  }

  /**
   * Callback to get page title for the name of the site.
   *
   * @param \Drupal\node\NodeInterface $site
   *   Site node.
   * @param \Drupal\node\NodeInterface $environment
   *   Evnironment node.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   *   Translated markup.
   */
  public function getPageTitle(NodeInterface $site, NodeInterface $environment) {
    return t('Backup environment - @site_title : @environment_title', ['@site_title' => $site->getTitle(), '@environment_title' => $environment->getTitle()]);
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, NodeInterface $environment = NULL) {
    $form_state->set('environment', $environment);

    $site_nid = $environment->field_shp_site->target_id;
    $backups = \Drupal::service('shp_custom.backup')->getAll($site_nid);

    $backup_options = [];
    foreach ($backups as $backup) {
      $backup_token = $backup['environment'] . '/' . $backup['backup'];
      $formatted_date_time = \Drupal::service('date.formatter')->format($backup['backup']);
      if ($environment = Node::load($backup['environment'])) {
        $backup_options[$backup_token] = $formatted_date_time . ' ' . $environment->getTitle();
      }
      else {
        $backup_options[$backup_token] = $formatted_date_time . ' Env:' . $backup['environment'];
      }
    }

    $build = [
      'backup' => [
        '#type' => 'select',
        '#title' => $this->t('Backup date'),
        '#options' => $backup_options,
        '#required' => TRUE,
      ],
      'submit' => [
        '#type' => 'submit',
        '#value' => $this->t('Restore now'),
      ],
    ];

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_state->cleanValues();

    $backup = $form_state->getValue('backup');

    // Find instances relating to the environment.
    $instance_ids = \Drupal::entityQuery('node')
      ->condition('type', 'shp_site_instance')
      ->condition('field_shp_environment', $form_state->get('environment')->Id())
      ->execute();

    if (count($instance_ids)) {
      // Run the restore from an arbitrary (first returned) instance.
      $instance = Node::load(current($instance_ids));
      // Set the backup to restore from.
      list($instance->backup_env_id, $instance->backup_timestamp) = explode('/', $backup);
      \Drupal::service('shp_custom.backup')->restore($instance);

      drupal_set_message($this->t('Restore has been queued for %title', [
        '%title' => $form_state->get('environment')->getTitle(),
      ]));
    }
    else {
      drupal_set_message($this->t('Restore failed. Could not find any instances for %title', [
        '%title' => $form_state->get('environment')->getTitle(),
      ]));
    }

    // Redirect back to where we started from. The site's environment page.
    $form_state->setRedirect(
      'view.shp_site_environments.page_1',
      ['node' => $form_state->get('environment')->field_shp_site->target_id]
    );
  }

}