<?php

namespace Drupal\advertiser_review\Plugin\Action;

use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Action\ActionBase;
use Drupal\Core\Annotation\Action;
use Drupal\Core\Annotation\Translation;
use Drupal\Core\Session\AccountInterface;

/**
 * Enables a review
 *
 * @Action(
 *   id = "review_enable_action",
 *   label = @Translation("Enable review"),
 *   type = "review"
 * )
 */
class EnableReview extends ActionBase
{

    /**
     * {@inheritdoc}
     */
    public function access($object, ?AccountInterface $account = null, $return_as_object = false): bool|AccessResultInterface
    {
        /**
   * @var \Drupal\advertiser_review\ReviewInterface $object 
*/
        $result = $object->access('update', $account, true);
        return $return_as_object ? $result : $result->isAllowed();
    }

    /**
     * {@inheritdoc}
     */
    public function execute($entity = null): void
    {
        if ($entity && $entity->hasField('status')) {
            $entity->set('status', true);
            $entity->save();
        }
    }

}
