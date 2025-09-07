<?php

namespace Drupal\advertiser_review\Controller;

use Drupal\advertiser_review\Service\ReviewManager;
use Drupal\Component\Render\MarkupInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Url;
use Drupal\node\NodeInterface;
use Drupal\views\Views;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

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
   * @param \Drupal\node\NodeInterface $node
   *   The agent node.
   * @param string $tab
   *   The tab name.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The JSON response.
   *
   * @throws \Exception
   */
  public function getTabContent(NodeInterface $node, $tab, Request $request): JsonResponse {
    if ($node->bundle() !== 'agent') {
      return new JsonResponse(['error' => 'Invalid node type'], 400);
    }

    // Get the page parameter from the request
    $page = (int) $request->query->get('page', 0);

    switch ($tab) {
      case 'properties':
        $content = $this->getPropertiesContent($node, $page);
        break;

      case 'reviews':
        $content = $this->getReviewsContent($node, $page);
        break;

      default:
        return new JsonResponse(['error' => 'Invalid tab'], 400);
    }

    return new JsonResponse(['content' => $content]);
  }

  /**
   * Get agent properties content.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The agent node.
   * @param int $page
   *   The page number for pagination.
   *
   * @return \Drupal\Component\Render\MarkupInterface|string
   *   The rendered content.
   *
   * @throws \Exception
   */
  protected function getPropertiesContent(NodeInterface $node, int $page = 0): MarkupInterface|string {
    // Load the view using Views::getView() for better control over pagination
    $view = Views::getView('adverts_view');

    if (!$view) {
      return '<div class="alert alert-danger">Properties view not found.</div>';
    }

    // Set the display
    $view->setDisplay('block_adverts_per_agent');

    // Set arguments (agent node ID)
    $view->setArguments([$node->id()]);

    // Set the current page for pagination (always set it, even for page 0)
    $view->setCurrentPage($page);

    // Execute the view
    $view->execute();

    // Get the rendered output
    $rendered_view = $view->render();

    return $this->renderer->render($rendered_view);
  }

  /**
   * Get agent reviews content.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The agent node.
   * @param int $page
   *   The page number for pagination.
   *
   * @return \Drupal\Component\Render\MarkupInterface|string
   *   The rendered content.
   *
   * @throws \Exception
   */
  protected function getReviewsContent(NodeInterface $node, int $page = 0): MarkupInterface|string {
    // Load the view using Views::getView() for better control over pagination
    $view = Views::getView('agent_reviews');

    if (!$view) {
      return '<div class="alert alert-danger">Agent reviews view not found.</div>';
    }

    // Set the display
    $view->setDisplay('block_agent_reviews');

    // Set arguments (agent node ID)
    $view->setArguments([$node->id()]);

    // Set the current page for pagination
    if ($page > 0) {
      $view->setCurrentPage($page);
    }

    // Execute the view
    $view->execute();

    // Get the rendered output
    $rendered_view = $view->render();

    // Build the complete content with the "Write a Review" link
    $build = [
      'view' => $rendered_view,
      'add_review_link' => [
        '#type' => 'link',
        '#title' => t('<i class="fa-solid fa-pencil"></i> Write a Review'),
        '#url' => Url::fromRoute('advertiser_review.add_form', ['node' => $node->id()]),
        '#attributes' => ['class' => ['btn', 'btn-success', 'text-white', 'mt-3']],
        '#weight' => 101,
      ],
      '#cache' => [
        'tags' => $node->getCacheTags(),
        'contexts' => ['user', 'url.query_args:page'],
      ],
    ];

    return $this->renderer->render($build);
  }
}
