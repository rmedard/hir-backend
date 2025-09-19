<?php

namespace Drupal\advertiser_review\Access;

use Drupal\advertiser_review\ReviewInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultAllowed;
use Drupal\Core\Access\AccessResultForbidden;
use Drupal\Core\Access\AccessResultNeutral;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;

class ReviewAccess implements AccessInterface {

  /**
   * Checks access for enable operation.
   */
  public function checkEnableAccess(AccountInterface $account, ReviewInterface $review = NULL): AccessResultForbidden|AccessResultNeutral|AccessResult|AccessResultAllowed {
    if ($review && $review->hasField('status')) {
      return AccessResult::allowedIf(!$review->get('status')->value)
        ->andIf(AccessResult::allowedIfHasPermission($account, 'administer review'));
    }
    return AccessResult::forbidden();
  }

  /**
   * Checks access for disable operation.
   */
  public function checkDisableAccess(AccountInterface $account, ReviewInterface $review = NULL): AccessResultForbidden|AccessResultNeutral|AccessResult|AccessResultAllowed {
    if ($review && $review->hasField('status')) {
      return AccessResult::allowedIf($review->get('status')->value)
        ->andIf(AccessResult::allowedIfHasPermission($account, 'administer review'));
    }
    return AccessResult::forbidden();
  }
}
