<?php

namespace Drupal\advertiser_review\Controller;

use Drupal\advertiser_review\Service\ReviewManager;
use Drupal\Component\Render\MarkupInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Url;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class AgentTabController extends ControllerBase {

  /**
   * The entity type manager.
   */
  protected $entityTypeManager;

  /**
   * The renderer service.
   */
  protected RendererInterface $renderer;

  /**
   * The review manager service.
   */
  protected ReviewManager $reviewManager;

  /**
   * Constructs an AgentTabController object.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, RendererInterface $renderer, ReviewManager $review_manager) {
    $this->entityTypeManager = $entity_type_manager;
    $this->renderer = $renderer;
    $this->reviewManager = $review_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): AgentTabController|static {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('renderer'),
      $container->get('advertiser_review.manager')
    );
  }

  /**
   * Get tab content for agent node.
   *
   * @throws \Exception
   */
  public function getTabContent(NodeInterface $node, $tab): JsonResponse {
    if ($node->bundle() !== 'agent') {
      return new JsonResponse(['error' => 'Invalid node type'], 400);
    }

    switch ($tab) {
      case 'properties':
        $content = $this->getPropertiesContent($node);
        break;

      case 'reviews':
        $content = $this->getReviewsContent($node);
        break;

      default:
        return new JsonResponse(['error' => 'Invalid tab'], 400);
    }

    return new JsonResponse(['content' => $content]);
  }

  /**
   * Get agent properties content.
   *
   * @throws \Exception
   */
  protected function getPropertiesContent(NodeInterface $node): MarkupInterface|string {
    // Load the view for agent properties
    $view = $this->entityTypeManager->getStorage('view')->load('adverts_view');

    if (!$view) {
      return '<div class="alert alert-danger">Properties view not found.</div>';
    }

    $build = [
      '#type' => 'view',
      '#name' => 'adverts_view',
      '#display_id' => 'block_adverts_per_agent',
      '#arguments' => [$node->id()],
      '#cache' => [
        'tags' => $node->getCacheTags(),
        'contexts' => ['user'],
      ],
    ];

    return $this->renderer->render($build);
  }

  /**
   * Get agent reviews content.
   *
   * @throws \Exception
   */
  protected function getReviewsContent(NodeInterface $node): MarkupInterface|string {
    // Load the view for agent reviews
    $view = $this->entityTypeManager->getStorage('view')->load('agent_reviews');
    if (!$view) {
      return '<div class="alert alert-danger">Agent reviews view not found.</div>';
    }

    $build = [
      '#type' => 'view',
      '#name' => 'agent_reviews',
      '#display_id' => 'block_agent_reviews',
      '#arguments' => [$node->id()],
      '#cache' => [
        'tags' => $node->getCacheTags(),
        'contexts' => ['user'],
      ],
    ];

    // Add the "Write a Review" link
     $build['add_review_link'] = [
       '#type' => 'link',
       '#title' => t('<i class="fa-solid fa-pencil"></i> Write a Review'),
       '#url' => Url::fromRoute('advertiser_review.add_form', ['node' => $node->id()]),
       '#attributes' => ['class' => ['btn', 'btn-success', 'text-white', 'mt-3']],
       '#weight' => 101,
     ];

    return $this->renderer->render($build);
  }


}
