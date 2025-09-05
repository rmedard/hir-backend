<?php

declare(strict_types=1);

namespace Drupal\advertiser_review\Service;

use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Core\Logger\LoggerChannelInterface;

/**
 * ReviewManager
 */
final readonly class ReviewManager {

  protected LoggerChannelInterface $logger;

  /**
   * Constructs a ReviewManager object.
   */
  public function __construct(private EntityTypeManagerInterface $entityTypeManager, private LoggerChannelFactory $loggerChannel) {
    $this->logger = $this->loggerChannel->get('advertiser_review');
  }

  /**
   * Get all reviews for a specific advertiser.
   *
   * @param int $advertiser_nid
   *   The node ID of the advertiser.
   *
   * @return \Drupal\advertiser_review\Entity\Review[]
   *   Array of review entities.
   */
  public function getReviewsForAdvertiser(int $advertiser_nid): array {
    try {
      $review_storage = $this->entityTypeManager->getStorage('review');
      $query = $review_storage->getQuery()
        ->condition('advertiser', $advertiser_nid)
        ->sort('created', 'DESC')
        ->accessCheck();

      $review_ids = $query->execute();
      return $review_storage->loadMultiple($review_ids);
    }
    catch (InvalidPluginDefinitionException|PluginNotFoundException $e) {
      $this->logger->error($e);
    }
    return [];
  }

  /**
   * Calculate average rating for an advertiser.
   *
   * @param int $advertiser_nid
   *   The node ID of the advertiser.
   *
   * @return float Average rating, or 0 if no reviews exist.
   *   Average rating, or 0 if no reviews exist.
   */
  public function getAverageRating(int $advertiser_nid): float {
    try {
      $review_storage = $this->entityTypeManager->getStorage('review');
      $query = $review_storage->getQuery()
        ->condition('advertiser', $advertiser_nid)
        ->accessCheck();

      $review_ids = $query->execute();

      if (empty($review_ids)) {
        return 0;
      }

      $reviews = $review_storage->loadMultiple($review_ids);
      $total_rating = 0;
      $count = 0;

      /**
       * @var \Drupal\advertiser_review\Entity\Review $review
       */
      foreach ($reviews as $review) {
        $total_rating += $review->get('rate')->value;
        $count++;
      }

      return $count > 0 ? round($total_rating / $count) : 0;
    }
    catch (InvalidPluginDefinitionException|PluginNotFoundException $e) {
      $this->logger->error($e);
      return 0;
    }
  }

  /**
   * @param int $advertiser_nid
   *
   * @return int
   */
  public function getReviewsCount(int $advertiser_nid): int {
    try {
      $review_storage = $this->entityTypeManager->getStorage('review');
      return $review_storage->getQuery()
        ->condition('advertiser', $advertiser_nid)
        ->accessCheck()
        ->count()
        ->execute();
    }
    catch (InvalidPluginDefinitionException|PluginNotFoundException $e) {
      $this->logger->error($e);
    }
    return 0;
  }

  /**
   * Update agent rating field.
   *
   * @param int $agent_id
   *   The agent node ID.
   */
  public function updateAgentRating(int $agent_id): void {
    try {
      /**
       * @var \Drupal\node\NodeInterface $agent_node
       */
      $agent_node = $this->entityTypeManager->getStorage('node')
        ->load($agent_id);

      if (!$agent_node || $agent_node->bundle() !== 'agent') {
        return;
      }

      $reviews = $this->getReviewsForAdvertiser($agent_id);

      if (empty($reviews)) {
        // No reviews, set to null/empty
        $agent_node->set('field_agent_review', [
          'average_rating' => NULL,
          'review_count' => 0,
        ]);
      }
      else {
        $total_rating = 0;
        $count = count($reviews);

        foreach ($reviews as $review) {
          $total_rating += intval($review->get('rate')->value);
        }

        $average_rating = $total_rating / $count;

        $agent_node->set('field_agent_review', [
          'average_rating' => $average_rating,
          'review_count' => $count,
        ]);
      }

      $agent_node->save();
    }
    catch (InvalidPluginDefinitionException|PluginNotFoundException|EntityStorageException $e) {
      $this->logger->error($e);
    }
  }

}
