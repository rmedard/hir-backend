<?php

namespace Drupal\advertiser_review;

use Drupal\Core\Action\ActionManager;
use Drupal\Core\Entity\EntityHandlerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a list builder for review entities
 */
class ReviewAction extends EntityListBuilder {

  /**
   * @var \Drupal\Core\Action\ActionManager
   */
  protected ActionManager $actionManager;

  /**
   * @param \Drupal\Core\Entity\EntityTypeInterface $entityType
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   * @param \Drupal\Core\Action\ActionManager $actionManager
   */
  public function __construct(EntityTypeInterface $entityType, EntityStorageInterface $storage, ActionManager $actionManager) {
    parent::__construct($entityType, $storage);
    $this->actionManager = $actionManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type): ReviewAction|EntityListBuilder|EntityHandlerInterface|static {
    return new static(
      $entity_type,
      $container->get('entity_type.manager')->getStorage($entity_type->id()),
      $container->get('plugin.manager.action')
    );
  }

  /**
   * {@inheritdoc}
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function getOperations(EntityInterface $entity): array {
    $operations = parent::getOperations($entity);
    if ($entity->hasField('status')) {
      $status = $entity->get('status')->value;

      if ($status) {
        $operations['disable'] = [
          'title' => $this->t('Disable'),
          'weight' => 10,
          'url' => $entity->toUrl('disable-form'),
        ];
      }
      else {
        $operations['enable'] = [
          'title' => $this->t('Enable'),
          'weight' => 10,
          'url' => $entity->toUrl('enable-form'),
        ];
      }
    }
    return $operations;
  }

}
