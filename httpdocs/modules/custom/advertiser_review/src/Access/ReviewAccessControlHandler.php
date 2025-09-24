<?php

namespace Drupal\advertiser_review\Access;

use Drupal\advertiser_review\ReviewInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultReasonInterface;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Access controller for the Review entity.
 */
class ReviewAccessControlHandler extends EntityAccessControlHandler {

  /**
   * @param \Drupal\advertiser_review\ReviewInterface|\Drupal\Core\Entity\EntityInterface $entity
   * @param $operation
   * @param \Drupal\Core\Session\AccountInterface $account
   *
   * @return \Drupal\Core\Access\AccessResult|\Drupal\Core\Access\AccessResultReasonInterface
   */
  protected function checkAccess(ReviewInterface|EntityInterface $entity, $operation, AccountInterface $account): AccessResultReasonInterface|AccessResult {
    switch ($operation) {
      case 'view':
        if ($entity->get('status')->value) {
          return AccessResult::allowedIfHasPermission($account, 'view review entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'administer review entities');
      case 'update':
        if ($entity->get('status')->value) {
          return AccessResult::allowedIfHasPermission($account, 'edit review entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'administer review entities');

      case 'delete':
        if ($entity->get('status')->value) {
          return AccessResult::allowedIfHasPermission($account, 'delete review entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'administer review entities');

      default:
        return AccessResult::neutral();
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL): AccessResultReasonInterface|AccessResult|\Drupal\Core\Access\AccessResultInterface {
    return AccessResult::allowedIfHasPermission($account, 'add review entities');
  }
}
