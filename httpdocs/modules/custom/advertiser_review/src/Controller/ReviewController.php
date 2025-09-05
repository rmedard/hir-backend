<?php

namespace Drupal\advertiser_review\Controller;

use Drupal;
use Drupal\Core\Controller\ControllerBase;
use Drupal\node\NodeInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ReviewController extends ControllerBase {

  /**
   * Provides a public review form for a specific advertiser.
   */
  public function addReviewForm(NodeInterface $node): array {

    if ($node->bundle() !== 'agent') {
      throw new NotFoundHttpException('Reviews can only be added to agents.');
    }

    $review = Drupal::entityTypeManager()
      ->getStorage('review')
      ->create([
        'advertiser' => $node,
      ]);

    $form = Drupal::entityTypeManager()
      ->getFormObject('review', 'add')
      ->setEntity($review);

    return Drupal::formBuilder()->getForm($form);
  }
}
