<?php

namespace Drupal\ua_sm_custom\Plugin\views\field;

use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Drupal\node\Entity\Node;

/**
 * Field handler to add the status of the environment as a whole.
 *
 * This works by querying site instance(s) state.
 *
 * @package Drupal\ua_sm_custom\Plugin\views\field
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("site_environment_status")
 */
class SiteEnvironmentStatus extends FieldPluginBase {

  /**
   * Stores the result of the query to reuse later.
   *
   * @var array
   */
  protected $build;

  /**
   * {@inheritdoc}
   */
  public function query() {}

  /**
   * {@inheritdoc}
   */
  public function preRender(&$values) {
    foreach ($values as $row) {
      $entity = $row->_entity;
      $environment = $entity->id();
      // Get instance ids.
      $instance_ids = \Drupal::entityQuery('node')
        ->condition('type', 'ua_sm_site_instance')
        ->condition('field_ua_sm_environment', $environment)
        ->execute();
      $instances = Node::loadMultiple($instance_ids);

      $building = 0;
      $running = 0;
      $failed = 0;

      foreach ($instances as $instance) {
        switch ($instance->field_ua_sm_state->value) {
          case 'starting':
            $building++;
            break;

          case 'running':
            $running++;
            break;

          case 'failed' || 'stopped' || 'stopping':
            $failed++;
            break;

          default:
            break;
        }
      }

      $this->build[$entity->id()] = [
        'failed' => $failed,
        'running' => $running,
        'building' => $building
      ];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    $entity = $values->_entity;
    $environment = $entity->id();
    $build['environment_status'] = [
      '#environment' => $environment,
      '#failed' => $this->build[$environment]['failed'],
      '#running' => $this->build[$environment]['running'],
      '#building' => $this->build[$environment]['building'],
      '#theme' => 'site_environment_status'
    ];
    return $build;
  }

}