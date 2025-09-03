<?php

namespace Drupal\rir_interface\Service;

use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Drupal\path_alias\PathAliasInterface;

class PathAliasService {


  /**
   * Drupal\Core\Logger\LoggerChannelFactory definition.
   *
   * @var LoggerChannelInterface
   */
  protected LoggerChannelInterface $logger;

  /**
   * @param \Drupal\Core\Logger\LoggerChannelFactory $loggerChannelFactory
   */
  public function __construct(LoggerChannelFactory $loggerChannelFactory) {
    $this->logger = $loggerChannelFactory->get('path_alias_service');
  }

  /**
   * @param \Drupal\path_alias\PathAliasInterface $pathAlias
   *
   * @return void
   */
  public function updateNodeRelativePath(PathAliasInterface $pathAlias): void {
    preg_match('/node\/(\d+)/', $pathAlias->getPath(), $matches);
    $node = Node::load($matches[1]);
    if ($node instanceof NodeInterface) {
      try {
        switch ($node->bundle()) {
          case 'advert':
            $node
              ->set('field_advert_relative_path', $pathAlias->getAlias())
              ->save();
            break;
          case 'agent':
            $node
              ->set('field_agent_relative_path', $pathAlias->getAlias())
              ->save();
            break;
          case 'property_request':
            $node
              ->set('field_pr_relative_path', $pathAlias->getAlias())
              ->save();
            break;
        }
      } catch (EntityStorageException $e) {
        $this->logger->error('Updating node relative path failed: ' . $e->getMessage());
      }
    }
  }
}