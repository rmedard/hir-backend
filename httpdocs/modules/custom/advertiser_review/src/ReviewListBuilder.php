<?php

declare(strict_types=1);

namespace Drupal\advertiser_review;

use Drupal;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Provides a list controller for the review entity type.
 */
final class ReviewListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader(): array {
    $header['id'] = $this->t('Review ID');
    $header['name'] = $this->t('Reviewer Name');
    $header['rate'] = $this->t('Rating');
    $header['advertiser'] = $this->t('Advertiser');
    $header['uid'] = $this->t('Author');
    $header['status'] = $this->t('Status');
    $header['created'] = $this->t('Created');
    $header['changed'] = $this->t('Updated');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity): array {
    /** @var \Drupal\advertiser_review\ReviewInterface $entity */
    $row['id'] = Link::createFromRoute(
      $entity->id(),
      'entity.review.canonical',
      ['review' => $entity->id()]
    );

    $row['name'] = $entity->get('name')->value;

    $row['rate'] = $entity->get('rate')->value . '/5';

    $advertiser = $entity->get('advertiser')->entity;
    $row['advertiser'] = Link::createFromRoute(
      $advertiser ? $advertiser->label() : '-',
      'entity.node.canonical',
      ['node' => $advertiser->id()]
    );

    $username_options = [
      'label' => 'hidden',
      'settings' => ['link' => $entity->get('uid')->entity->isAuthenticated()],
    ];
    $row['uid']['data'] = $entity->get('uid')->view($username_options);

    $row['status'] = $entity->get('status')->value ? $this->t('Enabled') : $this->t('Disabled');

    $row['created'] = Drupal::service('date.formatter')->format($entity->get('created')->value, 'short');
    $row['changed'] = Drupal::service('date.formatter')->format($entity->get('changed')->value, 'short');
    return $row + parent::buildRow($entity);
  }

}
