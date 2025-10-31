<?php

namespace Drupal\advertiser_review\Plugin\Action;

use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Action\ActionBase;
use Drupal\Core\Annotation\Action;
use Drupal\Core\Session\AccountInterface;

/**
 * Enables a review
 *
 * @Action(
 *   id = "review_disable_action",
 *   label = @Translation("Disable review"),
 *   type = "review"
 * )
 */
class DisableReview extends ActionBase {

  /**
   * {@inheritdoc}
   */
  public function access($object, ?AccountInterface $account = NULL, $return_as_object = FALSE): bool|AccessResultInterface {
    /** @var \Drupal\advertiser_review\ReviewInterface $object */
    $result = $object->access('update', $account, TRUE);
    return $return_as_object ? $result : $result->isAllowed();
  }

  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL): void {
    if ($entity && $entity->hasField('status')) {
      $entity->set('status', FALSE);
      $entity->save();
    }
  }

}
