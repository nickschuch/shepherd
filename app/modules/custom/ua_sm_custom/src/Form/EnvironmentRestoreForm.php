<?php

namespace Drupal\ua_sm_custom\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;

/**
 * Class SiteCloneForm.
 *
 * @package Drupal\ua_sm_custom\Form
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
    return 'ua_sm_custom_environment_restore_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, NodeInterface $environment = NULL) {
    $form_state->set('environment', $environment);

    $site_nid = $environment->field_ua_sm_site->target_id;
    $backups = \Drupal::service('ua_sm_custom.backup')->getAll($site_nid);

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
      ->condition('type', 'ua_sm_site_instance')
      ->condition('field_ua_sm_environment', $form_state->get('environment')->Id())
      ->execute();

    if (count($instance_ids)) {
      // Run the restore from an arbitrary (first returned) instance.
      $instance = Node::load(current($instance_ids));
      // Set the backup to restore from.
      list($instance->backup_env_id, $instance->backup_timestamp) = explode('/', $backup);
      \Drupal::service('ua_sm_custom.backup')->restore($instance);

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
      'view.ua_sm_site_environments.page_1',
      ['node' => $form_state->get('environment')->field_ua_sm_site->target_id]
    );
  }

}
