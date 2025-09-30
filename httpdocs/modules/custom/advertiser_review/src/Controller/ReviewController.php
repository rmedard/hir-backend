<?php

namespace Drupal\advertiser_review\Controller;

use Drupal;
use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
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

    try {
      $review = Drupal::entityTypeManager()
        ->getStorage('review')
        ->create([
          'advertiser' => $node,
        ]);

      $form = Drupal::entityTypeManager()
        ->getFormObject('review', 'add')
        ->setEntity($review);
      return Drupal::formBuilder()->getForm($form);
    } catch (InvalidPluginDefinitionException|PluginNotFoundException $e) {
      Drupal::logger('advertiser_review')->error($e->getMessage());
    }
    return [];
  }
}
